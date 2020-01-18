<?php

namespace App\Http\Controllers;

use App\Http\Controllers\helper\Reusable;
use App\Http\Controllers\helper\Validators;
use App\ReActions;
use App\Total;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReActionsController extends Controller
{
    public const SHA_256 = 'sha256';
    const FORBIDDEN = 'Forbidden';
    const DECRYPT_KEY = 'DECRYPT_KEY';
    const DECRYPT_IV = 'DECRYPT_IV';
    const ANONYMOUS = 'ANONYMOUS';
    private
        $body = NULL,
        $message = NULL,
        $statusCode = 400,
        $statusMessage = 'Bad Request';


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function setLike(request $request)
    {
        $Validator = Validators::validatorInReActions($request);

        $this->ifValidate($request, $Validator,2,1);

        return Reusable::returnDataInJson($this->body, $this->message, $this->statusCode, $this->statusMessage);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function setDislike(request $request)
    {
        $Validator = Validators::validatorInReActions($request);

        $this->ifValidate($request, $Validator,1,2);

        return Reusable::returnDataInJson($this->body, $this->message, $this->statusCode, $this->statusMessage);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getData(request $request)
    {
        $this->statusCode = 404;
        $this->statusMessage = 'Not Found';

        $nidValidator = Validators::nidValidatorTotal($request);

        $secretValidator = Validators::secretValidatorTotal($request);

        $this->renderDataIfNidIsValid($request, $nidValidator, $secretValidator);

        return Reusable::returnDataInJson($this->body, $this->message, $this->statusCode, $this->statusMessage);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getList(request $request)
    {
        $data = null;
        $nidList = $request->input('list');

        $this->renderListOfProduct($nidList, $data);

        return Reusable::returnDataInJson($this->body, $this->message, $this->statusCode, $this->statusMessage);
    }


    /**
     * @param $uuid
     * @param $nid
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getReActions($uuid, $nid)
    {
        return ReActions::query()->where('nid', $nid)->where('uuid', $uuid)->first() ?: null;
    }

    /**
     * @param int $currentStatus
     * @param int $CheckStatus
     * @param int $Status
     * @return int
     */
    public function setNewStatus(int $currentStatus, int $CheckStatus, int $Status): int
    {
        if ($currentStatus === $CheckStatus) {
            $newStatus = $Status;
        } else {
            $newStatus = $currentStatus === $Status ? 0 : $Status;
        }
        return $newStatus;
    }


    /**
     * @param $getReActions
     * @param int $newStatus
     */
    public function updateReActions($getReActions, int $newStatus)
    {
        $this->statusCode = 202;
        $this->statusMessage = 'Accepted';
        $this->message = __('dict.updated');

        $reActions = ReActions::query()->find($getReActions->id);
        $reActions->status = $newStatus;
        $reActions->change_number = $getReActions->change_number + 1; // first action
        $reActions->save();
    }

    /**
     * @param $nid
     * @param $uuid
     * @param int $status
     */
    public function insertReAction($nid, $uuid, int $status)
    {
        $this->statusCode = 201;
        $this->statusMessage = 'Created';
        $this->message = __('dict.created');

        $reActions = new ReActions();
        $reActions->nid = $nid;
        $reActions->uuid = $uuid;
        $reActions->status = $status;
        $reActions->change_number = 1; // first action
        $reActions->save();
    }


    /**
     * @param $nid
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getTotal($nid)
    {
        return Total::query()->where('nid', $nid)->first() ?: null;
    }


    /**
     * @param $nidList
     * @param $data
     */
    public function renderListOfProduct($nidList, $data): void
    {
        if (Validators::listValidator(['list' => $nidList])) {

            foreach ($nidList as $nid) {

                $totals = self::getTotal($nid);

                $data = $this->renderListIfTotalExist($data, $totals, $nid);

                $this->statusCode = 200;
                $this->statusMessage = 'OK';
            }
            $this->body = $data;
        }
    }


    /**
     * @param $getReActions
     * @param $nid
     * @param string $uuid
     * @param int $CheckStatus
     * @param int $Status
     */
    public function action($getReActions, $nid, string $uuid, int $CheckStatus, int $Status): void
    {
        if ($getReActions) {

            $this->statusCode = 429;
            $this->statusMessage = 'Too Many Requests';
            $this->message = __('dict.toManyRequest');

            $newStatus = self::setNewStatus($getReActions->status, $CheckStatus, $Status);

            if ($getReActions->change_number < 5) {

                $this->body = self::updateReActions($getReActions, $newStatus);

            }

        } else {
            $this->body = self::insertReAction($nid, $uuid, $Status);

        }
    }


    /**
     * @param Request $request
     * @param bool $Validator
     * @param int $CheckStatus
     * @param int $Status
     */
    public function ifValidate(request $request, bool $Validator, int $CheckStatus, int $Status): void
    {
        if ($Validator) {

            $this->statusCode = 403;
            $this->statusMessage = self::FORBIDDEN;

            $secret = $request->input('u');

            $uuid = Reusable::decrypt($secret, env(self::DECRYPT_KEY), env(self::DECRYPT_IV));

            $this->ifUserIsLoggedIn($request, $CheckStatus, $Status, $uuid);

        }
    }

    /**
     * @param $data
     * @param $totals
     * @param $nid
     * @return mixed
     */
    public function renderListIfTotalExist($data, $totals, $nid)
    {
        if ($totals) {
            $data [$nid] = [
                'like' => $totals->like,
                'dislike' => $totals->dislike,
                'total' => $totals->total,
                'wilson' => $totals->wilson,
            ];
        }
        return $data;
    }

    /**
     * @param Request $request
     * @param bool $secretValidator
     * @param string $uuid
     */
    public function renderDataIfUuidIsValid(request $request, bool $secretValidator, string $uuid): void
    {
        if ($secretValidator && Validators::uuidValidator(['uuid' => $uuid])) {

            $nid = $request->input('n');
            $actions = self::getReActions($uuid, $nid);
            $totals = self::getTotal($nid);

            $this->body = [
                'status' => $actions ? $actions->status : null,
                'like' => $totals->like,
                'dislike' => $totals->dislike,
                'total' => $totals->total,
                'wilson' => $totals->wilson,
            ];

            $this->statusCode = 200;
            $this->statusMessage = 'OK';
        }
    }

    /**
     * @param Request $request
     * @param bool $nidValidator
     * @param bool $secretValidator
     */
    public function renderDataIfNidIsValid(request $request, bool $nidValidator, bool $secretValidator): void
    {
        if ($nidValidator) {
            $secret = $request->input('u');
            $uuid = Reusable::decrypt($secret, env(self::DECRYPT_KEY), env(self::DECRYPT_IV));
            $this->statusCode = 403;
            $this->statusMessage = self::FORBIDDEN;

            $this->renderDataIfUuidIsValid($request, $secretValidator, $uuid);

        }
    }

    /**
     * @param Request $request
     * @param int $CheckStatus
     * @param int $Status
     * @param string $uuid
     */
    public function ifUserIsLoggedIn(request $request, int $CheckStatus, int $Status, string $uuid): void
    {
        if (Validators::uuidValidator(['uuid' => $uuid]) && $uuid != env(self::ANONYMOUS)) {

            $nid = $request->input('n');

            $getReActions = self::getReActions($uuid, $nid);

            $this->action($getReActions, $nid, $uuid, $CheckStatus, $Status);
        }
    }

}
