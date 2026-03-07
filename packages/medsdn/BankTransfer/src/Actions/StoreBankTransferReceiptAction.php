<?php

namespace Webkul\BankTransfer\Actions;

use Illuminate\Http\UploadedFile;
use Webkul\BankTransfer\Helpers\FileHelper;

class StoreBankTransferReceiptAction
{
    public function handle(UploadedFile $file, int $referenceId): array
    {
        $path = FileHelper::store($file, $referenceId);

        if (! $path) {
            throw new \RuntimeException(trans('banktransfer::app.shop.errors.upload-failed'));
        }

        return [
            'slip_path' => $path,
            'receipt_disk' => 'private',
            'receipt_name' => $file->getClientOriginalName(),
            'receipt_mime' => $file->getClientMimeType(),
            'receipt_size' => $file->getSize(),
        ];
    }
}
