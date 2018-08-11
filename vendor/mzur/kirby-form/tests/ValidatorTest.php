<?php

namespace Jevets\Kirby\Form\Tests;

use Jevets\Kirby\Validator;

class ValidatorTest extends TestCase
{
    public function testValidate()
    {
        $data = [
            'username' => 123,
            'email' => 'homersimpson.com',
            'zip' => 'abc',
            'website' => '',
        ];
        $rules = [
            'username' => ['alpha'],
            'email' => ['required', 'email'],
            'zip' => ['integer'],
            'website' => ['url'],
        ];
        $messages = [
            'username' => 'The username must not contain numbers',
            'email' => 'Invalid email',
            'zip' => 'The ZIP must contain only numbers',
        ];
        $result  = (new Validator($data, $rules, $messages))->validate();
        $this->assertEquals($messages, $result);
        $data = [
            'username' => 'homer',
            'email' => 'homer@simpson.com',
            'zip' => 123,
            'website' => 'http://example.com',
        ];
        $result  = (new Validator($data, $rules, $messages))->validate();
        $this->assertEquals([], $result);
    }

    public function testValidateSimple()
    {
        $data = ['homer', null];
        $rules = [['alpha'], ['required']];
        $result = (new Validator($data, $rules))->validate();
        $this->assertEquals(1, $result[1]);
    }

    public function testValidateRequired()
    {
        $rules = ['email' => ['required']];
        $messages = ['email' => ''];
        $result = (new Validator(['email' => null], $rules, $messages))->validate();
        $this->assertEquals($messages, $result);
        $result = (new Validator(['name' => 'homer'], $rules, $messages))->validate();
        $this->assertEquals($messages, $result);
        $result = (new Validator(['email' => ''], $rules, $messages))->validate();
        $this->assertEquals($messages, $result);
        $result = (new Validator(['email' => []], $rules, $messages))->validate();
        $this->assertEquals($messages, $result);
        $result = (new Validator(['email' => '0'], $rules, $messages))->validate();
        $this->assertEquals([], $result);
        $result = (new Validator(['email' => 0], $rules, $messages))->validate();
        $this->assertEquals([], $result);
        $result = (new Validator(['email' => false], $rules, $messages))->validate();
        $this->assertEquals([], $result);
        $result = (new Validator(['email' => 'homer@simpson.com'], $rules, $messages))->validate();
        $this->assertEquals([], $result);
    }

    public function testValidateOptions()
    {
        $rules = [
            'username' => ['min' => 6]
        ];
        $messages = ['username' => ''];
        $result  = (new Validator(['username' => 'homer'], $rules, $messages))->validate();
        $this->assertEquals($messages, $result);
        $result  = (new Validator(['username' => 'homersimpson'], $rules, $messages))->validate();
        $this->assertEquals([], $result);
        $rules = [
            'username' => ['between' => [3, 6]]
        ];
        $result  = (new Validator(['username' => 'ho'], $rules, $messages))->validate();
        $this->assertEquals($messages, $result);
        $result  = (new Validator(['username' => 'homersimpson'], $rules, $messages))->validate();
        $this->assertEquals($messages, $result);
        $result  = (new Validator(['username' => 'homer'], $rules, $messages))->validate();
        $this->assertEquals([], $result);
    }

    public function testValidateMultipleMessages()
    {
        $data = ['username' => ''];
        $rules = ['username' => ['required', 'alpha', 'min' => 4]];
        $messages = ['username' => [
            'The username is required',
            'The username must contain only letters',
            'The username must be at least 4 characters long',
        ]];
        $result = (new Validator(['username' => ''], $rules, $messages))->validate();
        $expected = ['username' => [
            'The username is required',
        ]];
        $this->assertEquals($expected, $result);
        $result = (new Validator(['username' => 'a1'], $rules, $messages))->validate();
        $expected = ['username' => [
            'The username must contain only letters',
            'The username must be at least 4 characters long',
        ]];
        $this->assertEquals($expected, $result);
        $result = (new Validator(['username' => 'ab'], $rules, $messages))->validate();
        $expected = ['username' => [
            'The username must be at least 4 characters long',
        ]];
        $this->assertEquals($expected, $result);
        $result = (new Validator(['username' => 'abcd'], $rules, $messages))->validate();
        $this->assertEquals([], $result);
    }
}
