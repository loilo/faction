<?php

Route::post('/webhook', 'WebhookController@handle')->name('webhook');
