<?php
/**
 * Created by PhpStorm.
 * User: Django
 * Date: 1/18/2020
 * Time: 10:05 AM
 */

namespace App\Http\Controllers\helper;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Validators
{
    /**
     * @param Request $request
     * @return bool
     */
    public static function validatorInReActions(request $request)
    {
        $validator = Validator::make($request->all(),
            ['n' => 'required|numeric|min:1', 'u' => 'required|min:64']
        );
        return !$validator->fails();
    }

    /**
     * @param $uuid
     * @return bool
     */
    public static function uuidValidator($uuid)
    {
        $validator = Validator::make($uuid,
            ['uuid' => 'uuid']
        );
        return !$validator->fails();
    }


    /**
     * @param Request $request
     * @return bool
     */
    public static function nidValidatorTotal(request $request)
    {
        $validator = Validator::make($request->all(),
            ['n' => 'required|numeric|exists:totals,nid']
        );
        return !$validator->fails();
    }


    /**
     * @param Request $request
     * @return bool
     */
    public static function secretValidatorTotal(request $request)
    {
        $validator = Validator::make($request->all(),
            ['u' => 'required|min:64']
        );
        return !$validator->fails();
    }


    /**
     * @param $list
     * @return bool
     */
    public static function listValidator($list)
    {
        $validator = Validator::make($list,
            ['list' => 'required|array|between:1,20', 'list.*' => 'numeric']

        );
        return !$validator->fails();
    }

}
