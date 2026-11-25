<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class ExpertMouPdf
{
    public function browser(): ?string
    {
        foreach (array_filter([
            config('services.mou_pdf.browser'),
            '/usr/bin/chromium',
            '/usr/bin/chromium-browser',
            '/usr/bin/google-chrome',
            'C:/Program Files/Google/Chrome/Application/chrome.exe',
            'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
        ]) as $path) {
            if (is_file($path) && is_executable($path)) {
                return $path;
            }
        }

        return null;
    }

    public function render(array $data, string $browser): string
    {
        $directory = storage_path('app/private/mou-'.Str::uuid());
        File::makeDirectory($directory, 0700, recursive: true);

        try {
            $html = $directory.'/contract.html';
            $pdf = $directory.'/contract.pdf';
            File::put($html, view('consultancy.contract-print', [
                ...$data,
                'fontCss' => $this->embeddedFontCss(),
                'forPdf' => true,
            ])->render());

            $fileUrl = 'file://'.(DIRECTORY_SEPARATOR === '\\' ? '/' : '')
                .str_replace('%2F', '/', rawurlencode(str_replace('\\', '/', $html)));
            if (DIRECTORY_SEPARATOR === '\\') {
                $fileUrl = str_replace('%3A', ':', $fileUrl);
            }

            (new Process([
                $browser, '--headless', '--disable-gpu', '--disable-background-networking',
                '--no-first-run', '--no-pdf-header-footer', '--user-data-dir='.$directory.'/profile',
                '--print-to-pdf='.$pdf, $fileUrl,
            ]))->setTimeout(30)->mustRun();

            $content = File::get($pdf);
            if (! str_starts_with($content, '%PDF-')) {
                throw new RuntimeException('The MoU renderer returned an invalid PDF.');
            }

            return $content;
        } finally {
            File::deleteDirectory($directory);
        }
    }

    private function embeddedFontCss(): string
    {
        $manifest = public_path('build/fonts-manifest.json');
        $fonts = is_file($manifest) ? File::json($manifest) : [];
        $css = $fonts['style']['familyStyles']['noto-sans-bengali'] ?? '';

        return preg_replace_callback('~url\("/build/(assets/[\w.-]+)"\)~', function (array $match): string {
            $font = public_path('build/'.$match[1]);
            $mime = str_ends_with($font, '.woff2') ? 'font/woff2' : 'font/woff';

            return is_file($font) ? 'url("data:'.$mime.';base64,'.base64_encode(File::get($font)).'")' : 'local("Noto Sans Bengali")';
        }, $css);
    }
}
