<?php

namespace App\Infrastructure;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Validator::extend('token_types', function ($attribute, $value, $parameters, $validator) {
            if (!is_array($value)) {
                return false;
            }

            foreach ($value as $v) {
                if (!isset($v['name']) || !isset($v['regex']) || !isset($v['skippable']) || !isset($v['priority'])) {
                    return false;
                }
            }

            return true;
        });

        Validator::extend('grammar', function ($attribute, $value, $parameters, $validator) {
            if (!is_array($value)) {
                return false;
            }

            if (!isset($value['productions']) || !isset($value['start_symbol'])) {
                return false;
            }

            return true;
        });

        Validator::extend('fa', function ($attribute, $value, $parameters, $validator) {
            if (!is_array($value)) {
                return false;
            }

            if (isset($value['states']) && isset($value['transitions'])) {
                foreach ($value['states'] as $s) {
                    if (!isset($s['id'])) {
                        return false;
                    }
                }

                foreach ($value['transitions'] as $t) {
                    if (!isset($t['src']) || !isset($t['char']) || !isset($t['dest'])) {
                        return false;
                    }
                }

                return true;
            }

            foreach ($value as $t) {
                if (!isset($t['src']) || !isset($t['char']) || !isset($t['dest'])) {
                    return false;
                }

                if (!isset($t['src']['id']) || !isset($t['dest']['id'])) {
                    return false;
                }
            }

            return true;
        });
    }
}
