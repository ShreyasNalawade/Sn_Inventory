<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class PdfUnlockController extends Controller
{
    /**
     * Show PDF unlock page.
     */
    public function index()
    {
        return view('admin.pdf-unlocker');
    }


    /**
     * Process multiple PDFs.
     */
    public function unlock(Request $request)
    {
        /*
         * Validate request
         */
        $request->validate([
            'pdfs' => [
                'required',
                'array',
                'min:1',
            ],

            'pdfs.*' => [
                'required',
                'file',
                'mimes:pdf',
                'max:51200',
            ],

            'password' => [
                'required',
                'string',
                'max:255',
            ],

        ], [

            'pdfs.required' =>
                'Please select at least one PDF.',

            'pdfs.*.mimes' =>
                'Only PDF files are allowed.',

            'pdfs.*.max' =>
                'Each PDF must be smaller than 50 MB.',

            'password.required' =>
                'Please enter the PDF password/code.',
        ]);


        /*
         * Python executable
         */
        $python = '/usr/bin/python3';


        /*
         * Python script
         */
        $script =
            base_path('scripts/unlock_pdf.py');


        /*
         * Make sure Python exists
         */
        if (!file_exists($python)) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Python was not found on the server.'
                );
        }


        /*
         * Make sure script exists
         */
        if (!file_exists($script)) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'PDF unlock script was not found.'
                );
        }


        /*
         * Temporary directory
         */
        $jobId =
            'pdf_' .
            date('Ymd_His') .
            '_' .
            bin2hex(random_bytes(5));


        $baseDirectory =
            storage_path(
                'app/pdf-unlocker/' . $jobId
            );


        $inputDirectory =
            $baseDirectory . '/input';


        $outputDirectory =
            $baseDirectory . '/output';


        File::makeDirectory(
            $inputDirectory,
            0750,
            true
        );


        File::makeDirectory(
            $outputDirectory,
            0750,
            true
        );


        $zipPath =
            $baseDirectory .
            '/unlocked_pdfs.zip';


        $successfulFiles = [];

        $failedFiles = [];


        try {

            /*
             * Password entered by user
             */
            $password =
                $request->input('password');


            /*
             * Process every uploaded PDF
             */
            foreach (
                $request->file('pdfs')
                as $index => $uploadedFile
            ) {

                $originalName =
                    $uploadedFile->getClientOriginalName();


                /*
                 * Generate safe filename
                 */
                $baseName =
                    pathinfo(
                        $originalName,
                        PATHINFO_FILENAME
                    );


                $baseName =
                    preg_replace(
                        '/[^A-Za-z0-9_\-]/',
                        '_',
                        $baseName
                    );


                if (!$baseName) {

                    $baseName = 'document';
                }


                $inputName =
                    $baseName .
                    '_' .
                    $index .
                    '.pdf';


                $outputName =
                    $baseName .
                    '_unlocked.pdf';


                $inputPath =
                    $inputDirectory .
                    '/' .
                    $inputName;


                $outputPath =
                    $outputDirectory .
                    '/' .
                    $outputName;


                /*
                 * Move uploaded file
                 */
                $uploadedFile->move(
                    $inputDirectory,
                    $inputName
                );


                /*
                 * Build Python command
                 *
                 * escapeshellarg() protects:
                 *
                 * input path
                 * output path
                 * password
                 */
                $command =
                    escapeshellarg($python)
                    . ' '
                    . escapeshellarg($script)
                    . ' '
                    . escapeshellarg($inputPath)
                    . ' '
                    . escapeshellarg($outputPath)
                    . ' '
                    . escapeshellarg($password)
                    . ' 2>&1';


                /*
                 * Execute Python
                 */
                $output = [];

                $returnCode = 0;


                exec(
                    $command,
                    $output,
                    $returnCode
                );


                /*
                 * Successful
                 */
                if (
                    $returnCode === 0
                    &&
                    file_exists($outputPath)
                    &&
                    filesize($outputPath) > 0
                ) {

                    $successfulFiles[] = [

                        'original' =>
                            $originalName,

                        'output' =>
                            $outputName,

                        'path' =>
                            $outputPath,
                    ];

                } else {

                    /*
                     * Failed
                     */
                    $reason =
                        implode(
                            "\n",
                            $output
                        );


                    if (
                        $returnCode === 2
                        ||
                        str_contains(
                            $reason,
                            'INVALID_PASSWORD'
                        )
                    ) {

                        $reason =
                            'Invalid PDF password/code.';

                    } elseif (!$reason) {

                        $reason =
                            'Unable to process this PDF.';
                    }


                    $failedFiles[] = [

                        'file' =>
                            $originalName,

                        'reason' =>
                            $reason,
                    ];
                }
            }


            /*
             * No successful PDFs
             */
            if (
                count($successfulFiles) === 0
            ) {

                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'No PDF could be unlocked. Please check the password/code.'
                    );
            }


            /*
             * Create ZIP
             */
            $zip =
                new ZipArchive();


            $zipResult =
                $zip->open(
                    $zipPath,
                    ZipArchive::CREATE |
                    ZipArchive::OVERWRITE
                );


            if (
                $zipResult !== true
            ) {

                throw new \RuntimeException(
                    'Could not create ZIP file.'
                );
            }


            /*
             * Add successful PDFs
             */
            foreach (
                $successfulFiles
                as $file
            ) {

                $zip->addFile(
                    $file['path'],
                    $file['output']
                );
            }


            $zip->close();


            /*
             * Download ZIP
             */
            return response()
                ->download(
                    $zipPath,
                    'unlocked_bank_pdfs.zip'
                )
                ->deleteFileAfterSend(true);


        } catch (\Throwable $e) {

            Log::error(
                'PDF unlock error',
                [
                    'message' =>
                        $e->getMessage(),
                ]
            );


            return back()
                ->withInput()
                ->with(
                    'error',
                    'An error occurred while processing the PDFs.'
                );


        } finally {

            /*
             * Delete temporary input/output files
             */
            if (
                File::exists(
                    $inputDirectory
                )
            ) {

                File::deleteDirectory(
                    $inputDirectory
                );
            }


            if (
                File::exists(
                    $outputDirectory
                )
            ) {

                File::deleteDirectory(
                    $outputDirectory
                );
            }
        }
    }
}