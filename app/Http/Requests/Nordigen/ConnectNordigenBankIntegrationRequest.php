<?php
/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2023. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license
 */

namespace App\Http\Requests\Nordigen;

use App\Http\Requests\Request;
use App\Libraries\MultiDB;
use App\Models\Company;
use Cache;

class ConnectNordigenBankIntegrationRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'lang' => 'string',
            'institution_id' => 'string',
            'redirect' => 'string',
        ];
    }

    // @turbo124 @todo please check for validity, when request from frontend
    public function prepareForValidation()
    {
        $input = $this->all();

        if (!array_key_exists('redirect', $input)) {
            $context = $this->getTokenContent();

            $input["redirect"] = isset($context["is_react"]) && $context['is_react'] ? config('ninja.react_url') . "/#/settings/bank_accounts" : config('ninja.app_url');

            $this->replace($input);
        }
    }
    public function getTokenContent()
    {
        if ($this->state) {
            $this->token = $this->state;
        }

        $data = Cache::get($this->token);

        return $data;
    }

    public function getCompany()
    {
        MultiDB::findAndSetDbByCompanyKey($this->getTokenContent()['company_key']);

        return Company::where('company_key', $this->getTokenContent()['company_key'])->firstOrFail();
    }
}