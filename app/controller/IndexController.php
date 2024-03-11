<?php

namespace app\controller;

# library
use support\Request;
# database & logic

class IndexController
{
    public function index(Request $request)
    {
        static $readme;
        if (!$readme) {
            $readme = "hey man";
            // $readme = "Version: v" . ENV("VERSION") . file_get_contents(base_path("README.md"));
            $readme = ENV("VERSION");
        }
        return $readme;
    }

    public function view(Request $request)
    {
        return view("index/view", ["name" => "webman"]);
    }

    public function json(Request $request)
    {
        return json(["code" => 0, "msg" => "ok"]);
    }
}
