<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocaleTest extends TestCase
{
    public function test_zona_waktu_aplikasi_adalah_jakarta(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));
        $this->assertSame('Asia/Jakarta', date_default_timezone_get());
    }

    public function test_bahasa_aplikasi_adalah_indonesia(): void
    {
        $this->assertSame('id', config('app.locale'));
        $this->assertSame('id', app()->getLocale());
    }
}
