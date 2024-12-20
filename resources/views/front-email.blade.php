@extends('layouts.app')
@section('content')
<style>
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
<div class="position-absolute start-0 end-0 start-0 bottom-0 w-100 h-100">
    <img src="{{ asset('images/logo-pam-dark.png') }}" alt="pam-logo" class="background-image" />
</div>

<div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5 position-relative mt-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xxl-4 col-lg-5">
                <div class="card">
                    <div class="card-body p-2" style="background-color: #000080;">
                        <h4 class="text-center"><a href="{{ route('email-page') }}"><img src="{{ asset('images/logo.png') }}" alt="pam-logo" height="100"></a></h4>
                        <h4 class="text-center text-white"><strong>Kartu Air Sehat</strong></h4>
                        {{-- <p class="text-muted mb-4">Enter your email address and we'll send you an email with instructions to reset your password.</p> --}}
                    </div>
                    
                    <div class="card-body p-2">
                        <form action="{{ route('validate-email') }}" method="POST" class="needs-validation" novalidate>
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label"><strong>Alamat Email</strong> <span class="text-danger">*</span></label>
                                <input class="form-control @error('email') is-invalid @enderror" 
                                       type="email" 
                                       id="email" 
                                       name="email" 
                                       placeholder="Silahkan isi email" 
                                       value="{{ old('email') }}" 
                                       required />
                                <div class="invalid-feedback">
                                    Mohon isi alamat email anda
                                </div>
                            </div>
                            <div class="captcha mb-2">
                                <span>{!! captcha_img() !!}</span>
                                <button type="button" class="btn btn-danger reload" id="reload">&#x21bb</button>
                            </div>
                            <div class="mb-3">
                                <input type="text" 
                                       class="form-control @error('captcha') is-invalid @enderror" 
                                       placeholder="Masukan Captcha" 
                                       name="captcha" 
                                       required />
                                {{-- <div class="invalid-feedback">
                                    Captcha wajib diisi
                                </div> --}}
                                @error('captcha')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="mb-3 text-center d-grid">
                                <button class="btn" type="submit" style="background-color: #0000b5;"><strong class="text-white"><i class="ri-login-box-line"></i> Masuk</strong></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@section('js-page')
<script>
    $('#reload').click(function(){
        $.ajax({
            type:'GET',
            url:'reload-captcha',
            success:function(data){
                $(".captcha span").html(data.captcha)
            }
        });
    });

    document.getElementById('reload').addEventListener('click', function () {
        fetch('{{ route("reload-captcha") }}')
        .then(response => response.json())
        .then(data => {
            document.querySelector('.captcha span').innerHTML = data.captcha;
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('.needs-validation');
        const emailInput = document.getElementById('email');
        const captchaInput = document.querySelector('input[name="captcha"]');

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        form.addEventListener('submit', function (e) {
            let formIsValid = true;

            if (!emailInput.value.trim()) {
                emailInput.classList.add('is-invalid');
                emailInput.nextElementSibling.textContent = 'Mohon isi alamat email anda';
                formIsValid = false;
            } else if (!isValidEmail(emailInput.value.trim())) {
                emailInput.classList.add('is-invalid');
                emailInput.nextElementSibling.textContent = 'Mohon masukkan alamat email yang valid';
                formIsValid = false;
            } else {
                emailInput.classList.remove('is-invalid');
                emailInput.classList.add('is-valid');
            }

            if (!captchaInput.value.trim()) {
                captchaInput.classList.add('is-invalid');
                if (!captchaInput.nextElementSibling) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = 'Captcha wajib diisi';
                    captchaInput.parentNode.appendChild(errorDiv);
                } else {
                    captchaInput.nextElementSibling.textContent = 'Captcha wajib diisi';
                }
                formIsValid = false;
            } else {
                captchaInput.classList.remove('is-invalid');
            }

            if (!formIsValid) {
                e.preventDefault();
            }
        });

        captchaInput.addEventListener('input', function () {
            if (!captchaInput.value.trim()) {
                captchaInput.classList.add('is-invalid');
                if (captchaInput.nextElementSibling) {
                    captchaInput.nextElementSibling.textContent = 'Captcha wajib diisi';
                }
            } else {
                captchaInput.classList.remove('is-invalid');
            }
        });
    });
</script>
@endsection
@endsection