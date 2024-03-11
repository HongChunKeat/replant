<?php

namespace plugin\dapp\app\controller;

# library
use Tinywan\Validate\Exception\ValidateException;
# database & logic
use app\model\logic\HelperLogic;

/**
 * Base Controller
 */
class Base
{
    protected $error = [];
    protected $successTotalCount = 0;
    protected $successPassedCount = 0;

    protected $response = ["success" => false, "data" => ["failed"], "msg" => ""];

    protected function validation($inputs = [], $rules = [])
    {
        try {
            $newRules = self::reinforceRules($rules);

            validate($inputs, $newRules, self::generateMsg($newRules));
        } catch (ValidateException $e) {
            $this->error[] = $e->getError();
        }
    }

    private function reinforceRules($rules)
    {
        $maxLength = 50;
        $newRules = [];

        foreach ($rules as $field => $rulesString) {
            //default must be first
            $newRules[$field] = $rulesString;

            //add on max
            if (
                !str_contains($rulesString, "max") &&
                !str_contains($rulesString, "length") &&
                !str_contains($rulesString, "in")
            ) {
                if (!empty($rulesString)) {
                    $newRules[$field] .= "|";
                }

                $newRules[$field] .= "max:" . $maxLength;
            }

            //add on greater than 0
            if (
                (str_contains($rulesString, "number") || str_contains($rulesString, "float"))
                && !str_contains($rulesString, "gt") && !str_contains($rulesString, "negative")
            ) {
                if (!empty($rulesString)) {
                    $newRules[$field] .= "|";
                }

                $newRules[$field] .= "gt:0";
            }

            //add on min 2 to date
            if (str_contains($rulesString, "date")) {
                if (!empty($rulesString)) {
                    $newRules[$field] .= "|";
                }

                $newRules[$field] .= "min:2";
            }

            // to allow negative value, remove negative rules
            if (str_contains($rulesString, "negative")) {
                $newRules[$field] = str_replace("negative", "", $rulesString);
            }
        }

        foreach ($newRules as $key => $value) {
            if ($value[0] == "|") {
                $newRules[$key] = substr($value, 1);
            }

            if ($value[strlen($value) - 1] == "|") {
                $newRules[$key] = substr($value, 0, -1);
            }

            if (str_contains($value, "||")) {
                $newRules[$key] = str_replace("||", "|", $value);
            }
        }

        return $newRules;
    }

    private function generateMsg($rules)
    {
        $reserveMsg = [
            "require" => "missing",
            "number" => "must_be_number",
            "float" => "must_be_number",
            "lt" => "must_be_less_than",
            "gt" => "must_be_greater_than",
            "egt" => "must_be_equal_greater_than",
            "min" => "min_length",
            "max" => "max_length",
            "length" => "invalid_length",
            "in" => "not_in_range",
            "email" => "must_be_email",
            "date" => "invalid_date",
            "ip" => "invalid_ip",
            "alphaNum" => "must_be_alphabet_and_number"
        ];
        $unknownError = "unknown_error";
        $messages = [];

        foreach ($rules as $field => $rulesString) {
            $fieldMessages = [];
            $fieldRules = explode("|", $rulesString);

            foreach ($fieldRules as $rule) {
                $parts = explode(":", $rule);
                $ruleName = $parts[0];
                $ruleValue = isset($parts[1]) ? $parts[1] : null;

                $messageKey = "{$field}.{$ruleName}";

                $showMsg = $reserveMsg[$ruleName] ?? $unknownError;
                $fieldMessages[$messageKey] = "$field:{$showMsg}";

                if ($ruleValue !== null) {
                    $fieldMessages[$messageKey] .= "|{$ruleValue}";
                }
            }

            $messages = array_merge($messages, $fieldMessages);
        }

        return $messages;
    }

    protected function output()
    {
        if ($this->error) {
            $this->response = [
                "success" => false,
                "data" => $this->error,
                "msg" => "error",
            ];
        }

        return json(HelperLogic::formatOutput($this->response));
    }
}