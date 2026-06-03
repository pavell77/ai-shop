<?php
/*
it('returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
*/

it('redirects from home to products page', function () {
    $response = $this->get('/');

    $response->assertRedirect('/products');
});

it('returns a successful response for products page', function () {
    $response = $this->get('/products');

    $response->assertStatus(200);
});