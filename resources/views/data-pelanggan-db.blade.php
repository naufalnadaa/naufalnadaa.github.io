@extends('layouts.app')
@section('content')
<style>
    .input-group .form-control {
        position: relative;
        padding-right: 30px;
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
    
    .position-absolute {
        z-index: -1;
        overflow: hidden;
    }

    .background-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.1;
        z-index: -1;
    }
</style>
<div id="preloader" style="display: none; ">
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
<div id="customer-pam">
<div class="account-pages pt-sm-5 pb-4 pb-sm-5 position-relative mt-5">
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
                        <h4 class="text-center"><strong>Data Pelanggan</strong></h4>
                        <form id="search">
                            @csrf
                            <div class="mb-3" hidden>
                                <label for="email" class="form-label">Alamat Email</label>
                                <input class="form-control bg-light" type="email" id="email" name="email" value="{{ $email }}" readonly/>
                            </div>
                            <div class="mb-3 mt-3">
                                <label class="form-label" for="customer_id"><strong>Nomor Konsumen</strong> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control numeric-customer-id" id="customer_id" name="customer_id" maxlength="10"/>
                                <div class="invalid-feedback">
                                    Mohon isi nomor konsumen.
                                </div>
                            </div>
                            <div class="mb-3 text-center">
                                <button type="submit" id="btn-search" class="btn btn-sm" style="background-color: #0000b5;">
                                    <strong class="text-white">
                                        <i class="ri-search-line"></i> Cari
                                    </strong>
                                </button>
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
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('search');
        const customerIdInput = document.getElementById('customer_id');
        const invalidFeedback = document.querySelector('.invalid-feedback');

        invalidFeedback.style.display = 'none';

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const customerId = customerIdInput.value;
            const email = document.getElementById('email').value;

            if (customerId === '') {
                const errorMessage = 'Mohon masukan nomor pelanggan valid.';
                invalidFeedback.textContent = errorMessage;
                invalidFeedback.style.display = 'block';
                customerIdInput.classList.add('is-invalid');
                return;
            }

            fetch(`/customer-page?customer_id=${customerId}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.st === '1') {
                    window.location.href = `/download-page?customer_id=${customerId}`;
                } else {
                    let errorMessage = '';

                    if (data.st === '2') {
                        errorMessage = 'Nomor konsumen salah.';
                    } else if (data.st === '0') {
                        errorMessage = 'Data pelanggan tidak ditemukan.';
                    }

                    invalidFeedback.textContent = errorMessage;
                    invalidFeedback.style.display = 'block';
                    customerIdInput.classList.add('is-invalid');

                    Swal.fire({
                        title: 'Error',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'Ok'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const errorMessage = 'Terjadi kesalahan saat mencari data.';
                invalidFeedback.textContent = errorMessage;
                invalidFeedback.style.display = 'block';
                customerIdInput.classList.add('is-invalid');
                Swal.fire({
                    title: 'Error',
                    text: 'Terjadi kesalahan saat mencari data.',
                    icon: 'error',
                    confirmButtonText: 'Ok'
                });
            });
        });
        customerIdInput.addEventListener('input', function () {
            const inputLength = this.value.length;

            this.classList.remove('is-invalid', 'is-valid');
            invalidFeedback.style.display = 'none';

            if (inputLength === 8 || inputLength === 9) {
                this.classList.add('is-valid');
            } else if (inputLength > 0) {
                this.classList.remove('is-invalid');
                this.classList.remove('is-valid');
                invalidFeedback.style.display = 'none';
            }
        });
    });
</script>
@endsection
@endsection