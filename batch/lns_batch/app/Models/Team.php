<?php

namespace App\Models;

class Team extends BaseModel
{
    protected $table      = 'teams';  // テーブル名
    protected $guarded    = [];

	const LOGO_STATUS_UNREGISTERED      = 0;
	const LOGO_STATUS_UNAUTHENTICATED   = 1;
	const LOGO_STATUS_AUTHENTICATED     = 2;
	const LOGO_STATUS_AUTHENTICATEERROR = 3;

}
