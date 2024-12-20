@extends('layouts.app')
@section('content')
<style>
    .input-group .form-control {
        position: relative;
        padding-right: 30px; /* Ruang untuk ikon */
    }

    .input-group .form-control:invalid {
        border-color: red;
        background-image: url('data:image/svg+xml;charset=UTF8,<svg xmlns="http://www.w3.org/2000/svg" fill="red" viewBox="0 0 16 16"><path d="M8 1a7 7 0 1 1 0 14A7 7 0 0 1 8 1zm0 1a6 6 0 1 0 0 12A6 6 0 0 0 8 2zM5.292 4.708a1 1 0 0 1 1.414 0L8 6.293l1.293-1.585a1 1 0 1 1 1.414 1.415L9.414 7.708l1.293 1.585a1 1 0 0 1-1.414 1.414L8 9.414l-1.293 1.585a1 1 0 0 1-1.414-1.414L6.586 7.708 5.293 6.293a1 1 0 0 1 0-1.585z"/></svg>');
        background-repeat: no-repeat;
        background-position: right 8px center;
        background-size: 16px;
    }

    .input-group .form-control:valid {
        border-color: green;
        background-image: url('data:image/svg+xml;charset=UTF8,<svg xmlns="http://www.w3.org/2000/svg" fill="green" viewBox="0 0 16 16"><path d="M16 8a8 8 0 1 1-16 0A8 8 0 0 1 16 8zM7.35 11.62L3.27 7.54a.8.8 0 0 1 1.15-1.15l2.77 2.77 5.43-5.43a.8.8 0 1 1 1.15 1.15L7.35 11.62z"/></svg>');
        background-position: right 8px center;
        background-size: 16px;
    }
    /* Fullscreen container */
    .position-absolute {
        z-index: -1; /* Ensure it's behind other content */
        overflow: hidden;
    }

    /* Background image styles */
    .background-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover; /* Ensures the image covers the entire screen */
        opacity: 0.1; /* Adjust transparency */
        z-index: -1; /* Keeps the image behind other elements */
    }
</style>
<div id="preloader">
    <div id="status">
        <div class="bouncing-loader">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
</div>
<div class="position-absolute start-0 end-0 start-0 bottom-0 w-100 h-100">
    <img src="{{ asset('images/logo-pam-dark.png') }}" alt="pam-logo" class="background-image" />
</div>

<div id="result-container">
<div class="account-pages pt-sm-3 position-relative">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xxl-5 col-lg-5">
                <div class="card">
                    <div class="card-body p-2" style="background-color: #000080;">
                        <h4 class="text-center"><img src="{{ asset('images/logo.png') }}" alt="pam-logo" height="100"></h4>
                        <h4 class="text-center text-white"><strong>Kartu Air Sehat</strong></h4>
                        {{-- <p class="text-muted mb-4">Enter your email address and we'll send you an email with instructions to reset your password.</p> --}}
                    </div>
                    <div class="card-body p-2">
                        <div class="" hidden>
                            <button type="button" id="btn-back" class="btn btn-primary btn-sm"> <i class=" ri-arrow-go-back-line"></i> Kembali </button>
                        </div>
                        <h4 class="text-center"><strong>Detail Data Pelanggan</strong></h4>
                        <form action="{{ route('file-download') }}" id="downloadFile" method="POST" class="needs-validation" novalidate>
                            @csrf
                            <div class="mb-3" hidden>
                                <label for="customer_id1" class="form-label"><strong>Nomor Konsumen</strong></label>
                                <input class="form-control bg-light" type="customer_id1" id="customer_id1" name="customer_id1" value="{{ $dataCustomer->customer_id }}" readonly/>
                            </div>
                            <div class="mb-3" hidden>
                                <label for="email1" class="form-label"><strong>Alamat Email</strong></label>
                                <input class="form-control bg-light" type="email1" id="email1" name="email1" value="{{ $email }}" readonly/>
                            </div>
                            <div id="result-container">
                                <div class="mb-3">
                                    <label class="form-label" for="customer_name"><strong>Nama Konsumen</strong></label>
                                    <input type="text" class="form-control bg-light" id="customer_name" name="customer_name" value="{{ $dataCustomer->customer_name }}" readonly/>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="address"><strong>Alamat Konsumen</strong></label>
                                    <textarea type="text" class="form-control bg-light" id="address" name="address" cols="3" readonly>{{ $dataCustomer->address }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="postal_code"><strong>Kode Pos</strong></label>
                                    <input type="text" class="form-control bg-light" id="postal_code" name="postal_code" value="{{ $dataCustomer->postal_code }}" readonly/>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="tarif_cd"><strong>Kode Tarif</strong></label>
                                    <input type="text" class="form-control bg-light" id="tarif_cd" name="tarif_cd" value="{{ $dataCustomer->tarif_cd }}" readonly/>
                                </div>
                            </div>
                                <div class="mb-3 text-center justify-end">
                                    <button class="btn btn-sm" id="btn-download" type="button" style="background-color: #0000b5;"><strong class="text-white"><i class="ri-download-line"></i> Unduh Kartu Air Sehat</strong></button>
                                </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

@section('js-page')
<script>
    $('#btn-download').on('click', function (e) {
        const preloader = document.getElementById('preloader');
        const status = document.getElementById('status');
        Swal.fire({
            title: 'Unduh Kartu Air Sehat',
            text: `Kartu akan dikirim lewat email`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Unduh Kartu!'
        }).then((result) => {
            if (result.isConfirmed) {
                preloader.style.display = 'block';
                status.style.display = 'block';
                $.ajax({
                    url: '/file-download',
                    type: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        email1: $('#email1').val(),
                        customer_id1: $('#customer_id1').val(),
                        customer_name: $('#customer_name').val(),
                        address: $('#address').val(),
                        postal_code: $('#postal_code').val(),
                        tarif_cd: $('#tarif_cd').val()
                    },
                    success: function (response) {
                        preloader.style.display = 'none';
                        status.style.display = 'none';

                        console.info("response: ", response);

                        const { status: serverStatus, message } = response;

                        if (serverStatus === 'success') {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: message || "Kartu Air Sehat berhasil dikirim via email.",
                                icon: 'success',
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    urlRedirect = '/customer-check-data';
                                    window.open(urlRedirect, '_self');
                                }
                            });
                            return;
                        }

                        Swal.fire({
                            title: 'Error!',
                            text: message || "Terjadi kesalahan, silahkan coba lagi nanti.",
                            icon: 'error',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        });
                    },
                    error: function (error) {
                        preloader.style.display = 'none';
                        status.style.display = 'none';

                        console.error("error: ", error);
                        Swal.fire({
                            title: 'Error!',
                            text: "Terjadi kesalahan server. Silakan coba lagi nanti.",
                            icon: 'error',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    });
</script>
@endsection
@endsection