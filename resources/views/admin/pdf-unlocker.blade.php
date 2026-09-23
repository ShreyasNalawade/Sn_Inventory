<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Bank PDF Unlocker</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #f4f6f9;

            color: #1f2937;
        }

        .container {
            width: 100%;
            max-width: 850px;

            margin: 50px auto;

            padding: 20px;
        }

        .card {
            background: white;

            border-radius: 14px;

            padding: 35px;

            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .header {
            text-align: center;

            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0 0 10px;

            font-size: 28px;
        }

        .header p {
            margin: 0;

            color: #6b7280;
        }

        .upload-area {

            border: 2px dashed #cbd5e1;

            border-radius: 12px;

            padding: 35px 20px;

            text-align: center;

            cursor: pointer;

            transition: 0.2s;

            background: #f8fafc;
        }

        .upload-area:hover {
            border-color: #2563eb;

            background: #eff6ff;
        }

        .upload-icon {
            font-size: 45px;

            margin-bottom: 10px;
        }

        .upload-area h3 {
            margin: 10px 0 5px;
        }

        .upload-area p {
            margin: 0;

            color: #6b7280;
        }

        #pdfs {
            display: none;
        }

        .file-list {
            margin-top: 20px;
        }

        .file-item {

            display: flex;

            justify-content: space-between;

            align-items: center;

            padding: 12px 15px;

            margin-bottom: 8px;

            border-radius: 8px;

            background: #f8fafc;

            border: 1px solid #e5e7eb;
        }

        .file-name {
            font-size: 14px;

            word-break: break-all;
        }

        .file-size {
            color: #6b7280;

            font-size: 12px;

            margin-left: 10px;

            white-space: nowrap;
        }

        .form-group {
            margin-top: 25px;
        }

        .form-group label {
            display: block;

            font-weight: 600;

            margin-bottom: 8px;
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {

            width: 100%;

            padding: 13px 45px 13px 14px;

            border: 1px solid #d1d5db;

            border-radius: 8px;

            font-size: 15px;

            outline: none;
        }

        .password-wrapper input:focus {

            border-color: #2563eb;

            box-shadow:
                0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .toggle-password {

            position: absolute;

            right: 12px;

            top: 50%;

            transform: translateY(-50%);

            border: 0;

            background: none;

            cursor: pointer;

            font-size: 18px;
        }

        .hint {

            margin-top: 7px;

            color: #6b7280;

            font-size: 12px;
        }

        .button {

            width: 100%;

            margin-top: 25px;

            padding: 14px;

            border: 0;

            border-radius: 8px;

            background: #2563eb;

            color: white;

            font-size: 16px;

            font-weight: 600;

            cursor: pointer;
        }

        .button:hover {
            background: #1d4ed8;
        }

        .button:disabled {

            background: #94a3b8;

            cursor: not-allowed;
        }

        .alert {

            padding: 14px;

            border-radius: 8px;

            margin-bottom: 20px;

            font-size: 14px;
        }

        .alert-error {

            background: #fee2e2;

            color: #991b1b;

            border: 1px solid #fecaca;
        }

        .validation-errors {

            background: #fff7ed;

            color: #9a3412;

            border: 1px solid #fed7aa;
        }

        .loading {

            display: none;

            margin-top: 20px;

            text-align: center;
        }

        .spinner {

            width: 35px;

            height: 35px;

            border: 4px solid #e5e7eb;

            border-top-color: #2563eb;

            border-radius: 50%;

            animation: spin 0.8s linear infinite;

            margin: auto;
        }

        @keyframes spin {

            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }

        }

        .security-note {

            margin-top: 20px;

            padding: 12px;

            background: #f0fdf4;

            border: 1px solid #bbf7d0;

            color: #166534;

            border-radius: 8px;

            font-size: 13px;

            line-height: 1.5;
        }

        @media(max-width: 600px) {

            .container {
                margin: 20px auto;

                padding: 10px;
            }

            .card {
                padding: 20px;
            }

            .file-item {
                flex-direction: column;

                align-items: flex-start;

                gap: 5px;
            }

            .file-size {
                margin-left: 0;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <div class="card">

        <div class="header">

            <h1>🔐 Bank PDF Unlocker</h1>

            <p>
                Upload multiple password-protected bank PDFs
                and download them as one ZIP.
            </p>

        </div>


        {{-- Error message --}}

        @if(session('error'))

            <div class="alert alert-error">

                {{ session('error') }}

            </div>

        @endif


        {{-- Validation errors --}}

        @if($errors->any())

            <div class="alert validation-errors">

                <strong>Please fix the following:</strong>

                <ul>

                    @foreach($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

            </div>

        @endif


        <form
        id="pdfForm"
        action="{{ route('pdf.unlock') }}"
        method="POST"
        enctype="multipart/form-data"
    >
    
        @csrf
    
        <input
            type="file"
            name="pdfs[]"
            accept="application/pdf,.pdf"
            multiple
            required
        >
    
        <input
            type="password"
            name="password"
            placeholder="Enter PDF code/password"
            required
        >
    
        <button type="submit">
            Extract & Download ZIP
        </button>
    
    </form>

    </div>

</div>


<script>

    const pdfInput =
        document.getElementById('pdfs');

    const fileList =
        document.getElementById('fileList');

    const form =
        document.getElementById('pdfForm');

    const submitButton =
        document.getElementById('submitButton');

    const loading =
        document.getElementById('loading');


    /*
     * Display selected files
     */
    pdfInput.addEventListener('change', function () {

        fileList.innerHTML = '';

        const files = this.files;

        if (!files.length) {

            return;
        }


        Array.from(files).forEach(function (file) {

            const item =
                document.createElement('div');

            item.className = 'file-item';


            const name =
                document.createElement('div');

            name.className = 'file-name';

            name.innerHTML =
                '📄 ' + escapeHtml(file.name);


            const size =
                document.createElement('div');

            size.className = 'file-size';

            size.textContent =
                formatFileSize(file.size);


            item.appendChild(name);

            item.appendChild(size);

            fileList.appendChild(item);

        });

    });


    /*
     * Password show/hide
     */
    function togglePassword() {

        const input =
            document.getElementById('password');

        if (input.type === 'password') {

            input.type = 'text';

        } else {

            input.type = 'password';

        }

    }


    /*
     * Form submit
     */
    form.addEventListener('submit', function () {

        if (!pdfInput.files.length) {

            alert('Please select at least one PDF.');

            event.preventDefault();

            return;
        }


        if (!document
            .getElementById('password')
            .value
        ) {

            alert('Please enter the PDF password/code.');

            event.preventDefault();

            return;
        }


        submitButton.disabled = true;

        submitButton.textContent =
            'Processing...';

        loading.style.display = 'block';

    });


    /*
     * Format file size
     */
    function formatFileSize(bytes) {

        if (bytes === 0) {

            return '0 Bytes';

        }

        const sizes = [
            'Bytes',
            'KB',
            'MB',
            'GB'
        ];

        const i =
            Math.floor(
                Math.log(bytes) /
                Math.log(1024)
            );

        return (
            parseFloat(
                (bytes / Math.pow(1024, i))
                    .toFixed(2)
            )
            + ' '
            + sizes[i]
        );

    }


    /*
     * Basic HTML escaping
     */
    function escapeHtml(text) {

        const div =
            document.createElement('div');

        div.textContent = text;

        return div.innerHTML;

    }

</script>

</body>

</html>