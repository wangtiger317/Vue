<?php

namespace Platform\Controllers\Staff;

use App\Customer;
use App\User;
use App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Platform\Controllers\Core;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Platform\Models\History;

class DataFormController extends \App\Http\Controllers\Controller
{
    /*
    |--------------------------------------------------------------------------
    | DataForm Controller
    |--------------------------------------------------------------------------
    |
    | This controller is a blue print for the custom Vue DataForm component.
    |
    */

    /**
     * This function generates the json response required for building the table form
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getDataForm(Request $request) {
        $model = $request->model;
        $uuid = $request->uuid;

        if (! class_exists($model)) {
            return response()->json(['status' => 'error', 'msg' => 'Class does not exist'], 422);
        }

        //$account = app()->make('account');
        $locale = request('locale', config('system.default_language'));
        app()->setLocale($locale);

        $settings = $model::getSettings()[auth()->user()->role];
        $permissions = $model::getPermissions()[auth()->user()->role];

        $actions = $model::getActions()[auth()->user()->role];
        $form = $model::getCreateForm()[auth()->user()->role];
        $extraSelectColumns = $model::getExtraSelectColumns()[auth()->user()->role];
        $extraQueryColumns = $model::getExtraQueryColumns()[auth()->user()->role];
        $translations = $model::getTranslations();
        $limitationName = method_exists($model, 'getLimitationName') ? $model::getLimitationName() : '';

        // Get columns for select query
        $select = [];
        $relations = [];
        $dates = [];
        foreach ($form as $tab) {
            foreach ($tab['subs'] as $sub) {
                foreach ($sub['items'] as $item) {

                    if ($item['type'] == 'description' || $item['type'] == 'image' || $item['type'] == 'images' || ($item['type'] == 'relation' && $item['relation']['type'] == 'belongsToMany')) {
                        // Do nothing because these types have no columns
                    } else {
                        $select[] = $item['column'];
                    }

                    if ($item['type'] == 'date_time') {
                        $dates[] = $item['column'];
                    }

                    // Relations
                    if ($item['type'] == 'relation') {
                        $relation = $item['relation'];
                        $permission = (isset($relation['permission'])) ? $relation['permission'] : 'personal';
                        $where = (isset($relation['where'])) ? $relation['where'] : '';

                        if ($relation['type'] == 'hasOne' || $relation['type'] == 'belongsTo') {
                            if ($permission == 'all') {
                                $items = $model::with($relation['with'])->getRelation($relation['with'])->selectRaw($relation['pk'] . ' as pk, ' . $relation['val'] . ' as val')->where(function($query) use($where) { if ($where != '') return $query->whereRaw($where); })->orderBy($relation['orderBy'], $relation['order'])->get();
                            } else {
                                $items = $model::with($relation['with'])->getRelation($relation['with'])->selectRaw('' . $relation['pk'] . ' as pk, ' . $relation['val'] . ' as val')->where(function($query) use($where) { if ($where != '') return $query->whereRaw($where); })->where('created_by', auth()->user()->id)->orderBy($relation['orderBy'], $relation['order'])->get();
                            }

                            $items = $items->map(function ($item) use ($relation) {
                                return ['pk' => $item->pk, 'val' => $item->val];
                            });

                            $items = $items->toArray();

                            $relations[$item['column']] = [
                                'column' => $item['column'],
                                'with' => $relation['with'],
                                'type' => $relation['type'],
                                'items' => $items
                            ];
                        }

                        if ($relation['type'] == 'belongsToMany') {
                            if ($permission == 'all') {
                                $items = DB::table($relation['table'])->selectRaw($relation['pk'] . ' as pk, ' . $relation['val'] . ' as val')->where(function($query) use($where) { if ($where != '') return $query->whereRaw($where); })->orderBy($relation['orderBy'], $relation['order'])->get();
                            } else {
                                $items = DB::table($relation['table'])->selectRaw('' . $relation['pk'] . ' as pk, ' . $relation['val'] . ' as val')->where(function($query) use($where) { if ($where != '') return $query->whereRaw($where); })->where('created_by', auth()->user()->id)->orderBy($relation['orderBy'], $relation['order'])->get();
                            }

                            $items = $items->map(function ($item) use ($relation) {
                                return ['pk' => $item->pk, 'val' => $item->val];
                            });

                            $items = $items->toArray();

                            $relations[$relation['with']] = [
                                'with' => $relation['with'],
                                'type' => $relation['type'],
                                'items' => $items
                            ];
                        }
                    }
                }
            }
        }

        // Defaults
        $values = [];

        if ($uuid !== null) {
            // Query model
            $query = $model::select(array_merge($select, $extraSelectColumns, $extraQueryColumns));

            // Permission to view all records
            if ($permissions['view'] == 'all') {
                // Get result
                $values = clone $query->withoutGlobalScopes()->whereUuid($uuid)->first();
                $allRecords = clone $query->whereUuid($uuid)->get();
            }

            // Only records from account can be viewed
            if ($permissions['view'] == 'account') {
                $values = clone $query->whereUuid($uuid)->first();
                //$allRecords = clone $query->get();
            }

            // Only records created by user can be viewed
            if ($permissions['view'] == 'personal') {
                $values = clone $query->withoutGlobalScopes()->whereUuid($uuid)->where('created_by', auth()->user()->id)->first();
                //$allRecords = clone $query->where('created_by', auth()->user()->id)->get();
            }

            // Only records created by user can be viewed
            if (Str::startsWith($permissions['view'], 'created_by:')) {
                $created_by = explode(':', $permissions['delete']);
                $created_by = explode(',', $created_by[1]);
                $values = clone $query->withoutGlobalScopes()->whereUuid($uuid)->whereIn('created_by', $created_by)->first();
                //$allRecords = clone $query->whereIn('created_by', $created_by)->get();
            }

        } else {
            // Query model
            $query = $model::select(array_merge($select, $extraSelectColumns, $extraQueryColumns));

            // Permission to view all records
            if ($permissions['view'] == 'all') {
                // Get result
                $allRecords = clone $query->withoutGlobalScopes()->get();
            }

            // Only records from account can be viewed
            if ($permissions['view'] == 'account') {
                $allRecords = clone $query->get();
            }

            // Only records created by user can be viewed
            if ($permissions['view'] == 'personal') {
                $allRecords = clone $query->withoutGlobalScopes()->where('created_by', auth()->user()->id)->get();
            }

            // Only records created by user can be viewed
            if (Str::startsWith($permissions['view'], 'created_by:')) {
                $created_by = explode(':', $permissions['delete']);
                $created_by = explode(',', $created_by[1]);
                $allRecords = clone $query->whereIn('created_by', $created_by)->get();
            }
        }

        if (empty($values)) {
            foreach ($form as $tab) {
                foreach ($tab['subs'] as $sub) {
                    foreach ($sub['items'] as $item) {
                        if ($item['type'] == 'description') {
                            // Do nothing
                        } elseif ($item['type'] == 'relation' && ($item['relation']['type'] == 'hasOne' || $item['relation']['type'] == 'belongsTo')) {
                            $values[$item['column'] . '_loading'] = false;
                            $values[$item['column'] . '_search'] = null;
                            $values[$item['column'] . '_items'] = [];
                        } elseif ($item['type'] == 'relation' && $item['relation']['type'] == 'belongsToMany') {
                            // No column
                            $values[$item['relation']['with'] . '_items'] = [];
                        } else {
                            $column = (strpos($item['column'], '->') !== false) ? str_replace('->', '___', $item['column']) : $item['column'];
                            $values[$column] = (isset($item['default'])) ? $item['default'] : '';
                        }
                    }
                }
            }
        }

        // Parse additional settings
        foreach ($form as $tab_key => $tab) {
            foreach ($tab['subs'] as $sub_key => $sub) {
                foreach ($sub['items'] as $i => $item) {
                    $new_column_name = isset($item['column']) ? $item['column'] : null;
                    // Parse JSON columns
                    if (isset($item['column']) && strpos($item['column'], '->') !== false) {
                        $columns = explode('->', $item['column']);
                        $column = 'json_unquote(json_extract(`' . $columns[0] . '`, \'$."' . $columns[1] . '"\'))';
                        //$column = $columns[0] . '->' . $columns[1];
                        $new_column_name = str_replace('->', '___', $item['column']);

                        //$value = (isset($values->{$column})) ? $values->{$column} : isset($values[$new_column_name]) ? $values[$new_column_name] : null;
                        $value = (isset($values->{$column})) ? $values->{$column} : $values[$new_column_name] ?? null;
                        switch ($value) {
                            case 'null':
                            case 'true':
                            case 'false':
                                $value = json_decode($value);
                                break;
                        }

                        $values[$new_column_name] = $value;

                        $form[$tab_key]['subs'][$sub_key]['items'][$i]['column'] = $new_column_name;

                        // Remove unparsed column
                        unset($values->{$column});
                    }

                    if ($item['type'] == 'currency') {
                        $fraction_digits = auth()->user()->getCurrencyFormat('fraction_digits');
                        $multiplier = str_pad(1, $fraction_digits + 1, 0);
                        $values[$new_column_name] = (is_numeric($values[$new_column_name])) ? number_format ($values[$new_column_name] / $multiplier, $fraction_digits) : $values[$new_column_name];
                    } elseif ($item['type'] == 'image') {
                        // Defaults
                        $values[$new_column_name . '_media_name'] = '';
                        $values[$new_column_name . '_media_url'] = '';
                        $values[$new_column_name . '_media_file'] = '';
                        $values[$new_column_name . '_media_changed'] = false;

                        // Check if media is attached
                        if ($uuid !== null) {
                            $media = $values->getFirstMedia($new_column_name);
                            if ($media !== null) {
                                $values[$new_column_name . '_media_name'] = $media->name;
                                $values[$new_column_name . '_media_url'] = $media->getFullUrl();
                            }
                        }
                    } elseif($item['type'] == 'images') {
                        // Defaults
                        $values[$new_column_name . '_media_name'] = '';
                        $values[$new_column_name . '_media_url'] = '';
                        $values[$new_column_name . '_media_file'] = '';
                        $values[$new_column_name . '_media_changed'] = false;

                        // Check if media is attached
                        if ($uuid !== null) {
                            //$media = $values->getFirstMedia($new_column_name);
                            //if ($media !== null) {
                            //$values[$new_column_name . '_media_name'] = $media->name;
                            //$values[$new_column_name . '_media_url'] = $media->getFullUrl();
                            //}
                        }
                    } elseif ($item['type'] == 'relation' && $item['relation']['type'] == 'belongsToMany') {
                        if ($uuid !== null) {
                            $values[$item['relation']['with']] = $values->{$item['relation']['with']}()->pluck($relation['remote_pk']);
                        }
                    } elseif ($item['type'] == 'password') {
                        $values[$new_column_name . '_show'] = false;
                    } elseif ($item['type'] == 'boolean') {
                        $values[$new_column_name] = (boolean) $values[$new_column_name];
                    } elseif ($item['type'] == 'date_time') {
                        if ($values[$new_column_name] != '') {
                            $value = Carbon::parse($values[$new_column_name], config('app.timezone'))->setTimezone(auth()->user()->getTimezone());
                            $values[$new_column_name] = $value->format('Y-m-d H:i:s');
                        } else {
                            $values[$new_column_name] = null;
                        }
                    }
                }
            }
        }

        // Parse form settings
        $form = $this->parseFormSettings($form, $uuid === null);

        // Remove unused columns
        $values = collect($values)->except($extraQueryColumns);

        // Limitations
        $limitReached = false;
        $limitationMax = -1;
        $count = 0;
        if ($limitationName != '' && $uuid === null) {
            $count = $allRecords->count();
            $limitationMax = auth()->user()->plan_limitations[$limitationName];
            $limitReached = ($count < $limitationMax) ? false : true;
        }

        // Transform translations
        $new_translations = [];
        foreach ($translations as $key => $translation) {
            $new_translations[$key] = $translation;
            $new_translations[$key . '_lowercase'] = strtolower($translation);
        }
        $translations = $new_translations;
        if($uuid !== null){
            $data = DB::table('customers')->where('customer_number', str_replace('-','', $values['number']))->get();
            $phone_number = $data[0]->mobile;
            $expiration = $data[0]->expiration;
            $customer_locale = $data[0]->locale;

            $campaign = \Platform\Models\Campaign::where('id', $data[0]->campaign_id)->first();
            $points = $campaign->pre_points;
            $values = [
                "name"=>$values['name'],
                "locale" => $customer_locale,
                "email" => $values['email'],
                "mobile" => $phone_number,
                "active" => $values['active'],
                "uuid" => $values['uuid'],
                "password_show" => $values['password_show'],
                "avatar_media_name" => $values['avatar_media_name'],
                "avatar_media_url" => $values['avatar_media_url'],
                "avatar_media_file" => $values['avatar_media_file'],
                "avatar_media_changed" => $values['avatar_media_changed'],
                "avatar" => $values['avatar'],
                "points" => $values['points'],
                "campaign_text" => $values['campaign_text'],
                "number" => $values['number'],
                "expiration" => $expiration,
                "pre_points" => $points
            ];
        }
        return response()->json(['status' => 'success', 'settings' => $settings, 'form' => $form, 'relations' => $relations, 'values' => $values, 'dates' => $dates, 'actions' => $actions, 'translations' => $translations, 'count' => $count, 'max' => $limitationMax, 'limitReached' => $limitReached], 200);
    }

    /**
     * Get relationship data
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * /
    public function postGetRelation(Request $request) {
    $model = $request->model;
    $uuid = $request->uuid;
    $relation = $request->relation;

    if (! class_exists($model)) {
    return response()->json(['status' => 'error', 'msg' => 'Class does not exist'], 422);
    }

    $locale = request('locale', config('system.default_language'));
    app()->setLocale($locale);

    $fields = [];

    if ($relation['type'] == 'hasOne') {
    $relation['pk'] = ($relation['pk'] == 'uuid') ? "LOWER(CONCAT(
    SUBSTR(HEX(uuid), 1, 8), '-',
    SUBSTR(HEX(uuid), 9, 4), '-',
    SUBSTR(HEX(uuid), 13, 4), '-',
    SUBSTR(HEX(uuid), 17, 4), '-',
    SUBSTR(HEX(uuid), 21)
    ))" : $relation['pk'];
    $fields = $model::with($relation['with'])->getRelation($relation['with'])->selectRaw($relation['pk'] . ' as pk, ' . $relation['val'] . ' as val')->orderBy($relation['orderBy'], $relation['order'])->get()->toArray();
    }

    return response()->json(['status' => 'success', 'fields' => $fields], 200);
    }*/

    /**
     * Parse form settings
     *
     * @return array
     */
    public function parseFormSettings($form, $create = true) {
        foreach ($form as $tab_key => $tab) {
            foreach ($tab['subs'] as $sub_key => $sub) {
                foreach ($sub['items'] as $i => $item) {
                    if (isset($item['column']) && strpos($item['column'], '->') !== false) {
                        $new_column_name = str_replace('->', '___', $item['column']);
                        //$form[$tab_key]['subs'][$sub_key]['items'][$i]['column'] = $new_column_name;
                    }

                    if ($create) {
                        // Validate
                        if (isset($item['validate_create'])) {
                            $form[$tab_key]['subs'][$sub_key]['items'][$i]['validate'] = $item['validate_create'];
                        }
                        // Hint
                        if (isset($item['hint_create'])) {
                            $form[$tab_key]['subs'][$sub_key]['items'][$i]['hint'] = $item['hint_create'];
                        }
                        // Required
                        if (isset($item['required_create'])) {
                            $form[$tab_key]['subs'][$sub_key]['items'][$i]['required'] = $item['required_create'];
                        }
                    } else {
                        // Validate
                        if (isset($item['validate_edit'])) {
                            $form[$tab_key]['subs'][$sub_key]['items'][$i]['validate'] = $item['validate_edit'];
                        }
                        // Hint
                        if (isset($item['hint_edit'])) {
                            $form[$tab_key]['subs'][$sub_key]['items'][$i]['hint'] = $item['hint_edit'];
                        }
                        // Required
                        if (isset($item['required_edit'])) {
                            $form[$tab_key]['subs'][$sub_key]['items'][$i]['required'] = $item['required_edit'];
                        }
                    }
                }
            }
        }
        return $form;
    }

    /**
     * Save post
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    /**
     * Handle user registration.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postSaveRecord(Request $request) {
        $model = $request->model;
        $uuid = ($request->uuid == 'null') ? null: $request->uuid;
        $request->formModel = (array) json_decode($request->formModel);

        $email = json_encode($request->formModel['email']);
        $account = app()->make('account');

        $language = ($request->language !== null) ? $request->language : config('system.default_language');
        $timezone = ($request->timezone !== null) ? $request->timezone : config('system.default_timezone');

        $verification_code = Str::random(32);

        $customer_number = Core\Secure::getRandom(9, '1234567890');


        if (! class_exists($model)) {
            return response()->json(['status' => 'error', 'msg' => 'Class does not exist'], 422);
        }

        if (env('APP_DEMO', false) === true && (auth()->user()->id == 1 || auth()->user()->id == 2)) {
            return response()->json(['status' => 'error', 'msg' => "This is a demo user. You can't update or delete anything this account. If you want to test all user features, sign up with a new account."], 422);
        }

        //$account = app()->make('account');
        $locale = request('locale', config('system.default_language'));
        app()->setLocale($locale);

        $settings = $model::getSettings()[auth()->user()->role];
        $permissions = $model::getPermissions()[auth()->user()->role];
        $actions = $model::getActions()[auth()->user()->role];
        $form = $model::getCreateForm()[auth()->user()->role];

        // Parse form settings
        $form = $this->parseFormSettings($form, $uuid === null);
        $campaign = \Platform\Models\Campaign::withoutGlobalScopes()->whereUuid(request('campaign_uuid', 0))->firstOrFail();
        if($uuid != null){
            $customer = Customer::whereUuid($uuid)->firstOrFail();
        }

        // Validation and values
        $validation = [];
        $values = [];
        foreach ($form as $tab) {
            foreach ($tab['subs'] as $sub_key => $sub) {
                foreach ($sub['items'] as $i => $item) {
                    $column = ($item['type'] == 'relation' && $item['relation']['type'] == 'belongsToMany') ? $item['relation']['with'] : $item['column'] ?? null;
                    $request_column = (isset($item['column']) && strpos($item['column'], '->') !== false) ? str_replace('->', '___', $column) : $column;
                    $relation = ($item['type'] == 'relation') ? $item['relation'] : null;

                    if (isset($item['validate'])) {
                        $item['validate'] = str_replace(['url: {require_protocol: false }', ': {require_protocol: true }'], '', $item['validate']);
                        if (strpos($item['validate'], 'decimal') !== false) {
                            $item['validate'] = str_replace('decimal', 'numeric', $item['validate']);
                        }
                        if (strpos($item['validate'], 'unique:') !== false && $uuid !== null) {
                            // Get ID for record that will be updated to exclude it from unique validation
                            $query = $model::withoutGlobalScopes()->whereUuid($uuid)->first();
                            $item['validate'] = $item['validate'] . ',' . $query->id;
                        }
                        $validation[$request_column] = $item['validate'];

                    }

                    $value = isset($request->formModel[$request_column]) ? $request->formModel[$request_column] : null;

                    if ($item['type'] == 'image') $value = isset($request->formModel[$column . '_media_file']) ? $request->formModel[$column . '_media_file'] : null;

                    if ($column !== null) {
                        $values[$column] = [
                            'value' => $value,
                            'type' => $item['type'],
                            'relation' => $relation
                        ];
                    }
                }
            }
        }

        $v = Validator::make($request->formModel, $validation);
        if($uuid==null){
            $v = Validator::make($request->formModel, [
                'email' => ['required', 'email', 'max:64', Rule::unique('customers')->where(function ($query) use ($campaign) {
                    return $query->where('campaign_id', $campaign->id);
                })
                ],
            ]);
        }else if($uuid!=null && $email != '"'.$customer->email.'"'){
            $v = Validator::make($request->formModel, [
                'email' => ['required', 'email', 'max:64', Rule::unique('customers')->where(function ($query) use ($campaign) {
                    return $query->where('campaign_id', $campaign->id);
                })
                ],
            ]);
        }else{
            $v = Validator::make($request->formModel, [
                'email' => ['required', 'email', 'max:64'],
            ]);
        }

        if ($v->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $v->errors()
            ], 422);
        }

        // Check if account limitations are reached
        $max = $campaign->user->plan_limitations['customers'];
        $count = count($campaign->user->customers);
//        echo $max;
//        echo $count; die();

        if($uuid == null && $count >= $max){
//            $email = new \stdClass;
//            $email->app_name = $account->app_name;
//            $email->app_url = '//' . $account->app_host;
//            $email->from_name = $account->app_mail_name_from;
//            $email->from_email = $account->app_mail_address_from;
//            $email->to_name = $campaign->user->name;
//            $email->to_email = $campaign->user->email;
//            $email->subject = "Account limitation reached";
//            $email->body_top = "A user (" . $request->email . ") could not sign up on https:" . $campaign->url . " because the maximum amount of customers (" . $max . ") has been reached.";
//            $email->cta_label = "Upgrade account";
//            $email->cta_url = '//' . $account->app_host . '/go#/billing';
//            $email->body_bottom = "Update your subscription to allow more customers to sign up.";
//
//            Mail::send(new \App\Mail\SendMail($email));

            return response()->json([
                'status' => 'error',
                'error' => "limitation_reached"
            ], 422);
        }

        $query = new $model;
        if ($uuid === null) { // Insert
            if ($permissions['create']) {


            }
        } else { // Update
            $query = false;

            // All records can be updated
            if ($permissions['update'] == 'all') {
                $query = $model::withoutGlobalScopes()->whereUuid($uuid)->first();
            }

            // Only records from account can be updated
            if ($permissions['update'] == 'account') {
                $query = $model::whereUuid($uuid)->first();
            }

            // Only records created by user can be updated
            if ($permissions['update'] == 'personal') {
                $query = $model::withoutGlobalScopes()->whereUuid($uuid)->where('created_by', auth()->user()->id)->first();
            }

            // Only records created by user can be updated
            if (Str::startsWith($permissions['update'], 'created_by:')) {
                $created_by = explode(':', $permissions['delete']);
                $created_by = explode(',', $created_by[1]);
                $query = $model::withoutGlobalScopes()->whereUuid($uuid)->whereIn('created_by', $created_by)->first();
            }

            if ($query === false) {
                return response()->json(['status' => 'error', 'msg' => 'No permission to edit record'], 422);
            }
        }

        foreach ($values as $column => $value) {
            if ($value['type'] == 'image') { // Process image upload
                // Check for changes
                if (isset($request->formModel[$column . '_media_changed']) && $request->formModel[$column . '_media_changed']) {
                    $file = $request->file($column);
                    if ($file !== null) {
                        $query
                            ->addMedia($file)
                            ->sanitizingFileName(function($fileName) {
                                return strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                            })
                            ->toMediaCollection($column, 'media');
                    } else {
                        $query
                            ->clearMediaCollection($column);
                    }
                }
            } elseif($value['type'] == 'images') { // Process multi image upload

            } elseif ($value['type'] == 'currency') {
                $multiplier = str_pad(1, auth()->user()->getCurrencyFormat('fraction_digits') + 1, 0);
                $query->{$column} = (is_numeric($value['value'])) ? $value['value'] * $multiplier : $value['value'];
            } elseif ($value['type'] == 'boolean') {
                $query->{$column} = ($value['value'] == '' || $value['value'] == null) ? 0 : 1;
            } elseif ($value['type'] == 'password') {
                if ($value['value'] != '') $query->{$column} = bcrypt($value['value']);
            } elseif ($value['type'] == 'date_time') {
                if ($value['value'] != '') {
                    $value = Carbon::parse($value['value'], auth()->user()->getTimezone())->setTimezone(config('app.timezone'));
                    $query->{$column} = $value->format('Y-m-d H:i:s');
                }
            } elseif ($value['type'] == 'relation' && $value['relation']['type'] == 'belongsToMany') {
                // Do nothing, sync afterwards because it's possible a new record without id yet
            } else {
                $query->{$column} = ($value['value'] == '') ? null : $value['value'];
            }
        }
        /*Add Customer*/
        $query->account_id = $account->id;

        $query->campaign_id = $campaign->id;
        $query->created_by = $campaign->created_by;

        $query->language = $language;
        $query->timezone = $timezone;
        $query->signup_ip_address = request()->ip();
        $query->verification_code = $verification_code;
        $query->customer_number = $customer_number;
        $query->locale = $locale;
        /*=============*/
        try{
            $query->save();
        }
        catch (\Exception $e){
            echo $e;
        }

        // Process after save
        foreach ($values as $column => $value) {
            if ($value['type'] == 'relation' && $value['relation']['type'] == 'belongsToMany') {
                $query->{$value['relation']['with']}()->sync($value['value']);
            }
        }

        return response()->json(['status' => 'success'], 200);
    }
}
