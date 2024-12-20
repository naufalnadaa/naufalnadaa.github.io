<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Mail;
use FPDF;

class KartuAirSehatController extends Controller
{
    public function emailPage(Request $request)
    {
        return view('front-email');
    }

    public function reloadCaptcha(Request $request)
    {
        return response()->json(['captcha'=>captcha_img()]);
    }

    public function validateEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'captcha' => 'required|captcha',
        ], [
            'email.required' => 'Mohon isi alamat email anda.',
            'captcha.required' => 'Captcha wajib diisi.',
            'captcha.captcha' => 'Captcha yang dimasukkan salah.',
        ]);

        // Simpan email dalam sesi
        session(['email' => $request->email]);

        // return to_route('check-data-konsumen');
        return to_route('check-data-konsumen-db');
    }

    public function cekDataKonsumen(Request $request)
    {
            // $fileExcel = public_path('/file/data_KAS_barat.xlsx');
            
            // $spreadsheet = IOFactory::load($fileExcel);
            // $sheet = $spreadsheet->getActiveSheet();

            // $rows = $sheet->toArray();

            // $header = $rows[0];
            // $data = array_slice($rows, 1);
        $email = session('email');
        return view('data-pelanggan', compact('email'));
    }

    public function cekDataKonsumenDB(Request $request)
    {
        $email = session('email');
        return view('data-pelanggan-db', compact('email'));
    }

    public function searchDataPelanggan(Request $request)
    {
        $customer_id = trim((string) $request->input('customer_id'));

        if (strlen($customer_id) == 8) {
            $fileExcel = public_path('/file/data_KAS_timur.xlsx');
            $cacheKey = 'data_KAS_timur';
        } elseif (strlen($customer_id) == 9) {
            $fileExcel = public_path('/file/data_KAS_barat.xlsx');
            $cacheKey = 'data_KAS_barat';
        } else {
            return response()->json(['status' => 'invalid_customer_id']);
        }

        if (!file_exists($fileExcel)) {
            return response()->json(['status' => 'file_not_found']);
        }

        $data = Cache::remember($cacheKey, 3600, function () use ($fileExcel) {
            try {
                $reader = IOFactory::createReader('Xlsx');
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($fileExcel);
                $sheet = $spreadsheet->getActiveSheet();

                $allData = [];
                foreach ($sheet->getRowIterator() as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(true);

                    $rowData = [];
                    foreach ($cellIterator as $cell) {
                        $rowData[] = is_numeric($cell->getValue()) ? (string) $cell->getValue() : trim($cell->getValue());
                    }

                    if (!empty($rowData[0])) {
                        $allData[] = [
                            'customer_id' => $rowData[0] ?? '',
                            'customer_name' => $rowData[1] ?? '',
                            'address' => $rowData[2] ?? '',
                            'postal_code' => $rowData[3] ?? '',
                            'tarif_cd' => $rowData[4] ?? '',
                        ];
                    }
                }

                return $allData;
            } catch (\Exception $ex) {
                throw new \RuntimeException("Error processing Excel file: " . $ex->getMessage());
            }
        });

        $found = collect($data)->firstWhere('customer_id', $customer_id);

        if ($found) {
            return response()->json([
                'status' => 'success',
                'data' => $found
            ]);
        }

        return response()->json([
            'status' => 'not_found'
        ]);
    }

    public function customerPage(Request $request)
    {
        $email = session('email');
        $customer_id = $request->input('customer_id');

        $customer_id_length = strlen($customer_id);

        if ($customer_id_length == 8) {
            $dataCustomer = DB::table('data_kas_timur')
                ->select('*')
                ->where('customer_id', $customer_id)
                ->first();
        } elseif ($customer_id_length == 9) {
            $dataCustomer = DB::table('data_kas_barat')
                ->select('*')
                ->where('customer_id', $customer_id)
                ->first();
        } else {
            return response()->json([
                'st' => '2',
                'message' => 'Nomor konsumen salah.'
            ]);
        }

        if (!$dataCustomer) {
            return response()->json([
                'st' => '0',
                'message' => 'Data pelanggan tidak ditemukan.'
            ]);
        }

        return response()->json([
            'st' => '1',
            'redirect_url' => route('download-page', ['customer_id' => $customer_id])
        ]);
    }

    public function downloadPage(Request $request)
    {
        $customer_id = $request->input('customer_id');
        $email = session('email');

        $customer_id_length = strlen($customer_id);

        if ($customer_id_length == 8) {
            $dataCustomer = DB::table('data_kas_timur')
                ->where('customer_id', $customer_id)
                ->first();
        } elseif ($customer_id_length == 9) {
            $dataCustomer = DB::table('data_kas_barat')
                ->where('customer_id', $customer_id)
                ->first();
        } 

        if (!$dataCustomer) {
            abort(404, 'Data pelanggan tidak ditemukan.');
        }

        return view('download-page', compact('dataCustomer', 'email'));
    }

    public function fileDownload(Request $request)
    {
        $email = $request->input('email1');
        $customer_id = $request->input('customer_id1');
        $customer_name = $request->input('customer_name');
        $address = $request->input('address');
        $postal_code = $request->input('postal_code');
        $tarif_cd = $request->input('tarif_cd');

        DB::beginTransaction();

        try {
            DB::table('t_download_log')->insert([
                'customer_id' => $customer_id,
                'email' => $email,
                'download_time' => DB::raw('NOW()')
            ]);

            $logo = public_path('images/logo.png');
            $facebook = public_path('images/facebook.png');
            $twitter = public_path('images/twitter.png');
            $instagram = public_path('images/instagram.png');
            $linkedin = public_path('images/linkedin.png');
            $kas_back_path = public_path('kasCard/KAS-Back.jpg');
            $kas_front_path = public_path('kasCard/KAS-Front.jpg');

            $front_image = imagecreatefromjpeg($kas_front_path);

            $black = imagecolorallocate($front_image, 0, 0, 0);
            $fontPathCustId = public_path('fonts/Fontspring-DEMO-organetto-ultrabold.otf');
            $fontPathCustName = public_path('fonts/argentum-sans.black.ttf');

            $fontSize = 40;
            $x = 200;
            $y = 100;
            imagettftext($front_image, $fontSize, 0, $x, $y, $black, $fontPathCustId, $customer_id);

            $fontSizeName = 18;
            $x_name = 205;
            $y_name = 130;
            imagettftext($front_image, $fontSizeName, 0, $x_name, $y_name, $black, $fontPathCustName, $customer_name);

            $output_front_image = public_path('kasCard/KAS-' . $customer_id . '.jpg');
            imagejpeg($front_image, $output_front_image);
            imagedestroy($front_image);

            $back_image = imagecreatefromjpeg($kas_back_path);

            $width = imagesx($back_image);
            $height = imagesy($back_image);
            $gap = 50;

            $combined_image = imagecreatetruecolor($width, ($height * 2) + $gap);

            $white = imagecolorallocate($combined_image, 255, 255, 255);
            imagefilledrectangle($combined_image, 0, 0, $width, ($height * 2) + $gap, $white);

            imagecopy($combined_image, imagecreatefromjpeg($output_front_image), 0, 0, 0, 0, $width, $height);
            imagecopy($combined_image, $back_image, 0, $height + $gap, 0, 0, $width, $height);
            imagedestroy($back_image);

            $tempCombinedImagePath = sys_get_temp_dir() . '/KAS-' . $customer_id . '-combined.jpg';
            imagejpeg($combined_image, $tempCombinedImagePath);
            imagedestroy($combined_image);

            $pdf = new FPDF();
            $pdf->AddPage();
            $pdf->Image($tempCombinedImagePath, 10, 10, 190);

            $pdfOutputPath = public_path('kasPdf/KAS-' . $customer_id . '.pdf');
            $pdf->Output('F', $pdfOutputPath);

            unlink($tempCombinedImagePath);

            $data = [
                'customer_id' => $customer_id,
                'customer_name' => $customer_name,
                'address' => $address,
                'postal_code' => $postal_code,
                'tarif_cd' => $tarif_cd,
                'logo' => $logo,
                'facebook' => $facebook,
                'twitter' => $twitter,
                'instagram' => $instagram,
                'linkedin' => $linkedin,
                'kas_back' => $kas_back_path,
                'kas_front' => public_path('kasCard/KAS-' . $customer_id . '.jpg')
            ];

            Mail::send('kas-email', $data, function ($message) use ($email, $pdfOutputPath) {
                $message->to($email)->subject('Kartu Air Sehat - PAM JAYA');
                $message->attach($pdfOutputPath);
            });

            DB::commit();
            return response()->json(["status" => 'success', "message" => "Email berhasil dikirim."]);
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json(["status" => 'error', "message" => $ex->getMessage()]);
        }
    }
}
