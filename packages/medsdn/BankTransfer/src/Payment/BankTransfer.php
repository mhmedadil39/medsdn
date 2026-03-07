<?php

namespace Webkul\BankTransfer\Payment;

use Webkul\Payment\Payment\Payment;

class BankTransfer extends Payment
{
    /**
     * Payment method code.
     *
     * @var string
     */
    protected $code = 'banktransfer';

    /**
     * Get redirect url.
     *
     * @return string|null
     */
    public function getRedirectUrl()
    {
        return null;
    }

    /**
     * Get configured bank accounts.
     *
     * @return array
     */
    public function getBankAccounts(): array
    {
        $accounts = [];

        for ($i = 1; $i <= 3; $i++) {
            $name = $this->getConfigData("bank_{$i}_name");

            if (empty($name)) {
                continue;
            }

            $accounts[] = [
                'bank_name'       => $name,
                'branch_name'     => $this->getConfigData("bank_{$i}_branch"),
                'account_holder'  => $this->getConfigData("bank_{$i}_account_holder"),
                'account_number'  => $this->getConfigData("bank_{$i}_account_number"),
                'iban'            => $this->getConfigData("bank_{$i}_iban"),
            ];
        }

        return $accounts;
    }

    /**
     * Check if payment method is available.
     *
     * @return bool
     */
    public function isAvailable()
    {
        if (! $this->getConfigData('active')) {
            return false;
        }

        // Validate that at least one bank account is configured
        $accounts = $this->getBankAccounts();

        if (empty($accounts)) {
            return false;
        }

        // Validate that at least one account has all required fields
        foreach ($accounts as $account) {
            if (
                ! empty($account['bank_name'])
                && ! empty($account['branch_name'])
                && ! empty($account['account_holder'])
                && ! empty($account['account_number'])
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get payment method additional details for checkout display.
     *
     * @return array
     */
    public function getAdditionalDetails()
    {
        $accounts = $this->getBankAccounts();

        if (empty($accounts)) {
            return [];
        }

        $details = [];

        // Add instructions if configured
        $instructions = $this->getConfigData('instructions');
        if (! empty($instructions)) {
            $details[] = [
                'title' => trans('banktransfer::app.shop.checkout.instructions'),
                'value' => $instructions,
            ];
        }

        // Add bank accounts
        foreach ($accounts as $index => $account) {
            $accountDetails = [];

            if (! empty($account['bank_name'])) {
                $accountDetails[] = trans('banktransfer::app.shop.checkout.bank-name') . ': ' . $account['bank_name'];
            }

            if (! empty($account['branch_name'])) {
                $accountDetails[] = trans('banktransfer::app.shop.checkout.branch-name') . ': ' . $account['branch_name'];
            }

            if (! empty($account['account_holder'])) {
                $accountDetails[] = trans('banktransfer::app.shop.checkout.account-holder') . ': ' . $account['account_holder'];
            }

            if (! empty($account['account_number'])) {
                $accountDetails[] = trans('banktransfer::app.shop.checkout.account-number') . ': ' . $account['account_number'];
            }

            if (! empty($account['iban'])) {
                $accountDetails[] = trans('banktransfer::app.shop.checkout.iban') . ': ' . $account['iban'];
            }

            if (! empty($accountDetails)) {
                $details[] = [
                    'title' => trans('banktransfer::app.shop.checkout.bank-account') . ' ' . ($index + 1),
                    'value' => implode('<br>', $accountDetails),
                ];
            }
        }

        return $details;
    }
}
