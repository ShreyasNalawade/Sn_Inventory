<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sandip oil Depo | Login</title>
    <link rel="icon" href="/assets/icon/favicon-bg-remove.png" type="image/png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- SweetAlert2 CSS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(to bottom, #f3d7d7, #f7f1e3);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: sans-serif;
            color: #333;
        }

        .login-container {
            text-align: center;
            padding: 2rem;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }

        .app-icon {
            width: 120px;
            height: 120px;
            background: #fff;
            border-radius: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            margin-bottom: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .pin-inputs {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .pin-input {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #e0e0e0;
            border: none;
            outline: none;
            text-align: center;
            font-size: 1.5rem;
            color: #333;
        }

        .pin-input:focus {
            outline: 2px solid #ff6347;
        }

        .form-control {
            border-radius: 10px;
            text-align: center;
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }

        .btn-continue {
            background-color: #ff6347;
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 30px;
            font-size: 1.2rem;
            width: 100%;
            box-shadow: 0 4px 10px rgba(255, 99, 71, 0.4);
        }

        .forgot-pin {
            color: #888;
            text-decoration: none;
            font-size: 0.9rem;
            margin-top: 1rem;
            display: block;
        }
    </style>
</head>

<body>

    <div class="login-container">
        <div class="app-icon mx-auto">
            <img src="/assets/icon/SN-logo.png" alt="App Icon" class="img-fluid" style="border-radius: 20px" />
        </div>
        @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form id="pin-form" action="{{ route('login.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <input type="tel" class="form-control @error('mobileNumber') is-invalid @enderror" id="mobileNumber"
                    name="mobileNumber" placeholder="Mobile Number" maxlength="10" value="{{ old('mobileNumber') }}"
                    required />
            </div>
            <h6 class="mb-4">Enter Your PIN</h6>

            <div class="pin-inputs">
                <input type="password" class="pin-input" maxlength="1" id="pin1" inputmode="numeric" pattern="[0-9]*" />
                <input type="password" class="pin-input" maxlength="1" id="pin2" inputmode="numeric" pattern="[0-9]*" />
                <input type="password" class="pin-input" maxlength="1" id="pin3" inputmode="numeric" pattern="[0-9]*" />
                <input type="password" class="pin-input" maxlength="1" id="pin4" inputmode="numeric" pattern="[0-9]*" />
            </div>
            <input type="hidden" name="pin" id="hiddenPin" />

            @if ($errors->any())
            <div class="alert alert-danger mt-3 p-2 text-center">
                @foreach ($errors->all() as $error)
                {{ $error }}<br>
                @endforeach
            </div>
            @endif

            <button type="submit" class="btn btn-continue mt-4">Continue</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            // Select all PIN inputs
            const pinInputs = $(".pin-input");

            // Handle input and focus
            pinInputs.on("input", function (e) {
                const currentInput = $(this);
                const nextInput = currentInput.next(".pin-input");

                // Sanitize input to allow only digits
                let value = currentInput.val();
                if (!/^\d*$/.test(value)) {
                    currentInput.val(value.replace(/[^\d]/g, ""));
                }

                // Move to the next input if a digit is entered
                if (currentInput.val().length === 1 && nextInput.length) {
                    nextInput.focus();
                }
            });

            // Handle backspace
            pinInputs.on("keydown", function (e) {
                const currentInput = $(this);
                const prevInput = currentInput.prev(".pin-input");

                if (e.key === "Backspace" || e.key === "Delete") {
                    // If the current input is empty and backspace is pressed, move to the previous input
                    if (currentInput.val() === "" && prevInput.length > 0) {
                        prevInput.focus();
                    }
                }
            });

            // Form submission logic
            $("#pin-form").on("submit", function (e) {
                // Combine PIN digits
                let pinCode = "";
                pinInputs.each(function () {
                    pinCode += $(this).val();
                });

                // Set the combined PIN to the hidden input before submitting
                $("#hiddenPin").val(pinCode);
            });
        });
    </script>
</body>

</html>