<?php

namespace Jevets\Kirby\Form\Tests;

use Kirby\Toolkit\V;
use Jevets\Kirby\Validator;

class HelperTest extends TestCase
{
    public function testFunction()
    {
        $this->assertTrue(function_exists('csrf_field'));
    }

    public function testCsrfField()
    {
        // the token should not be regenerated during a single request
        $this->assertEquals(csrf_field(), csrf_field());
        $this->assertStringContainsString('value="abc"', csrf_field('abc'));
    }

    public function testFileValidator()
    {
        $this->assertTrue(V::file([
            'name' => 'testname',
            'type' => 'text/plain',
            'size' => 0,
            'tmp_name' => 'qwert',
            'error' => UPLOAD_ERR_OK,
        ]));

        $this->assertTrue(V::file([
            'name' => 'testname',
            'type' => 'text/plain',
            'size' => 0,
            'tmp_name' => 'qwert',
            'error' => UPLOAD_ERR_NO_FILE,
        ]));

        $this->assertFalse(V::file([
            'type' => 'text/plain',
            'size' => 0,
            'tmp_name' => 'qwert',
            'error' => UPLOAD_ERR_OK,
        ]));

        $this->assertFalse(V::file([
            'name' => 'testname',
            'size' => 0,
            'tmp_name' => 'qwert',
            'error' => UPLOAD_ERR_OK,
        ]));

        $this->assertFalse(V::file([
            'name' => 'testname',
            'type' => 'text/plain',
            'tmp_name' => 'qwert',
            'error' => UPLOAD_ERR_OK,
        ]));

        $this->assertFalse(V::file([
            'name' => 'testname',
            'type' => 'text/plain',
            'size' => 0,
            'error' => UPLOAD_ERR_OK,
        ]));

        $this->assertFalse(V::file([
            'name' => 'testname',
            'type' => 'text/plain',
            'size' => 0,
            'tmp_name' => 'qwert',
        ]));

        $codes = [
            UPLOAD_ERR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE,
            UPLOAD_ERR_PARTIAL,
            UPLOAD_ERR_NO_TMP_DIR,
            UPLOAD_ERR_CANT_WRITE,
            UPLOAD_ERR_EXTENSION,
        ];

        foreach ($codes as $code) {
            $this->assertFalse(V::file([
                'name' => 'testname',
                'type' => 'text/plain',
                'size' => 0,
                'tmp_name' => 'qwert',
                'error' => $code,
            ]));
        }
    }

    public function testRequiredFileValidator()
    {
        $this->assertTrue(V::requiredFile([
            'name' => 'testname',
            'type' => 'text/plain',
            'size' => 0,
            'tmp_name' => 'qwert',
            'error' => UPLOAD_ERR_OK,
        ]));

        $this->assertFalse(V::requiredFile([
            'name' => 'testname',
            'type' => 'text/plain',
            'size' => 0,
            'tmp_name' => 'qwert',
            'error' => UPLOAD_ERR_NO_FILE,
        ]));
    }

    public function testFilesizeValidator()
    {
        $this->assertTrue(V::filesize(['size' => 9000, 'error' => UPLOAD_ERR_OK], 9));
        $this->assertFalse(V::filesize(['size' => 9000, 'error' => UPLOAD_ERR_OK], 8));
        // If no file was uploaded, validation should still pass.
        $this->assertTrue(V::filesize(['size' => 9000, 'error' => UPLOAD_ERR_NO_FILE], 8));
        $this->assertFalse(V::filesize([], 8));
        $this->assertFalse(V::filesize('asdf', 8));
    }

    public function testMimeValidator()
    {
        // This works without an actual file because the Toolkit guesses the MIME by
        // file extension in this case.
        $this->assertTrue(V::mime(['tmp_name' => 'test.txt', 'error' => UPLOAD_ERR_OK], ['text/plain']));
        // If no file was uploaded, validation should still pass.
        $this->assertTrue(V::mime(['tmp_name' => 'test.json', 'error' => UPLOAD_ERR_NO_FILE], ['text/plain']));
        $this->assertTrue(V::mime('test.txt', ['text/plain']));
        $this->assertFalse(V::mime('test.txt', ['image/png']));
        // Test handling of non-array argument through invalid().
        $validator = new Validator(['file' => 'test.txt'], ['file' => ['mime' => ['text/plain']]]);
        $this->assertEquals([], $validator->validate());
    }

    public function testImageValidator()
    {
        $path = sys_get_temp_dir().'/kirby_test_image';
        file_put_contents($path, 'sometext');
        $this->assertFalse(V::image($path));
        // This is a GIF: http://probablyprogramming.com/2009/03/15/the-tiniest-gif-ever
        file_put_contents($path, base64_decode('R0lGODlhAQABAIABAP///wAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=='));
        $this->assertTrue(V::image($path));
        $this->assertTrue(V::image(['tmp_name' => $path, 'error' => UPLOAD_ERR_OK]));
        $this->assertTrue(V::image(['tmp_name' => '', 'error' => UPLOAD_ERR_NO_FILE]));
        unlink($path);
    }
}
