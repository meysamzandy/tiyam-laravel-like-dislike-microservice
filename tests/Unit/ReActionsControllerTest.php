<?php
/**
 * Created by PhpStorm.
 * User: Django
 * Date: 1/12/2020
 * Time: 1:36 PM
 */

namespace App\Http\Controllers\tets\Unit;


use App\Http\Controllers\helper\Reusable;
use App\Http\Controllers\helper\Validators;
use App\Http\Controllers\ReActionsController;
use App\ReActions;
use App\Total;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ReActionsControllerTest extends TestCase
{

    const VOTES = '_api/_v1/votes';
    private $reActions;
    private $reActionsFirst;
    private $total;
    private $totalFirst;
    private $reActionsController;

    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:fresh --seed');
        $this->reActions = new ReActions();
        $this->reActionsFirst = $this->reActions->all()->first();
        $this->total = new  Total();
        $this->totalFirst = $this->total->all()->first();
        $this->reActionsController = new ReActionsController();
    }


    public function testGetData()
    {


        $request = new Request([], $_GET, [], [], [], []);
        $request->headers->set('Content-Type', 'application/json');


        $request->query->set('n', '0e0');
        $request->query->set('u', $this->reActionsFirst->uuid);
        $votes = $this->reActionsController->getData($request);
        $this->assertNotEmpty($votes->getContent());
        $this->assertJson($votes->getContent());
        $this->assertEquals(404, $votes->getStatusCode());
        $this->assertJsonStringEqualsJsonString('{"body":null,"message":null}', $votes->getContent());



        $request->query->set('n', $this->reActionsFirst->nid);
        $request->query->set('u', '0e0');
        $votes = $this->reActionsController->getData($request);
        $this->assertNotEmpty($votes->getContent());
        $this->assertJson($votes->getContent());
        $this->assertEquals(403, $votes->getStatusCode());
        $this->assertJsonStringEqualsJsonString('{"body":null,"message":null}', $votes->getContent());


        $request->query->set('n', $this->reActionsFirst->nid);
        $request->query->set('u', (new Reusable())->encrypt($this->reActionsFirst->uuid, env('DECRYP_KEY'), env('DECRYPT_IV')));
        $votes = $this->reActionsController->getData($request);
        $this->assertNotEmpty($votes->getContent());
        $this->assertJson($votes->getContent());
        $this->assertEquals(403, $votes->getStatusCode());
        $this->assertJsonStringEqualsJsonString('{"body":null,"message":null}', $votes->getContent());


        $request->query->set('n', $this->reActionsFirst->nid);
        $request->query->set('u', (new Reusable())->encrypt($this->reActionsFirst->uuid, env('DECRYPT_KEY'), env('DECRYPT_IV')));
        $votes = $this->reActionsController->getData($request);
        $this->assertNotEmpty($votes->getContent());
        $this->assertJson($votes->getContent());
        $this->assertEquals(200, $votes->getStatusCode());
        $this->assertEquals(true, $votes->isOk());
        $this->assertJsonStringEqualsJsonString('{"body":{"status":' . $this->reActionsFirst->status . ',"like":' . $this->totalFirst->like . ',"dislike":' . $this->totalFirst->dislike . ',"total":' . $this->totalFirst->total . ',"wilson":' . $this->totalFirst->wilson . '},"message":null}', $votes->getContent());

    }

    // test if user has reactions

    public function testGetReActions()
    {

        $reActions = $this->reActionsController->getReActions('0e0', '0e0');
        $this->assertNull($reActions);

        $reActions = $this->reActionsController->getReActions('0e0', $this->reActionsFirst->nid);
        $this->assertNull($reActions);

        $reActions = $this->reActionsController->getReActions($this->reActionsFirst->uuid, '0e0');
        $this->assertNull($reActions);

        $reActions = $this->reActionsController->getReActions($this->reActionsFirst->uuid, $this->reActionsFirst->nid);
        $this->assertNotEmpty($reActions);

    }

    // test

    public function testGetTotal()
    {

        $reActions = $this->reActionsController->getTotal('0e0');
        $this->assertNull($reActions);

        $reActions = $this->reActionsController->getTotal($this->reActionsFirst->nid);
        $this->assertNotNull($reActions);

    }

    public function testGetList()
    {
        // check if there is no data in body
        $request = new Request([], $_POST, [], [], [], []);
        $request->headers->set('Content-Type', 'application/json');
        $reActionsController = new ReActionsController();
        $setLike = $reActionsController->getList($request);
        $this->assertJson($setLike->getContent());
        $this->assertEquals($setLike->getStatusCode(), 400);

        // check if there is data in body but array size lower than 1
        $data = '{ "list": [ ] }';
        $request = new Request([], $_POST, [], [], [], [], $data);
        $request->headers->set('Content-Type', 'application/json');
        $reActionsController = new ReActionsController();
        $setLike = $reActionsController->getList($request);
        $this->assertJson($setLike->getContent());
        $this->assertEquals($setLike->getStatusCode(), 400);

        // check if there is data in body but no valid
        $data = '{ "list": [ abcdef ] }';
        $request = new Request([], $_POST, [], [], [], [], $data);
        $request->headers->set('Content-Type', 'application/json');
        $reActionsController = new ReActionsController();
        $setLike = $reActionsController->getList($request);
        $this->assertJson($setLike->getContent());
        $this->assertEquals($setLike->getStatusCode(), 400);

        // check if there is data in body but no valid
        $data = '{ "list": [ 457685, 457685, 457685, 457685, 457685, 457685, 457685, 457685, 457685, 457685,
        457685, 457685, 457685, 457685, 457685, 457685, 457685, 457685, 457685, 457685, 457685 ] }';
        $request = new Request([], $_POST, [], [], [], [], $data);
        $request->headers->set('Content-Type', 'application/json');
        $reActionsController = new ReActionsController();
        $setLike = $reActionsController->getList($request);
        $this->assertJson($setLike->getContent());
        $this->assertEquals($setLike->getStatusCode(), 400);

        // check if there is data in body and valid
        $data = '{ "list": [ '.$this->reActionsFirst->nid.' ] }';
        $request = new Request([], $_POST, [], [], [], [], $data);
        $request->headers->set('Content-Type', 'application/json');
        $reActionsController = new ReActionsController();
        $getList = $reActionsController->getList($request);
        $this->assertJson($getList->getContent());
        $this->assertEquals($getList->getStatusCode(), 200);
        $this->assertJsonStringEqualsJsonString('{ "body": { "'.$this->reActionsFirst->nid.'": { "like": ' . $this->totalFirst->like . ', "dislike": ' . $this->totalFirst->dislike . ', "total": ' . $this->totalFirst->total . ', "wilson": ' . $this->totalFirst->wilson . ' } }, "message": null }', $getList->getContent());


    }

    public function testSetLike()
    {
        // check if there is no data in body
        $request = new Request([], $_POST, [], [], [], []);
        $request->headers->set('Content-Type', 'application/json');
        $reActionsController = new ReActionsController();
        $setLike = $reActionsController->setLike($request);
        $this->assertJson($setLike->getContent());
        $this->assertEquals($setLike->getStatusCode(), 400);

        // check if there is data in body but no value
        $data = '{"n":"","u":""}';
        $request = new Request([], $_POST, [], [], [], [], $data);
        $request->headers->set('Content-Type', 'application/json');
        $reActionsController = new ReActionsController();
        $setLike = $reActionsController->setLike($request);
        $this->assertJson($setLike->getContent());
        $this->assertEquals($setLike->getStatusCode(), 400);

        // check if there is data in body but nid is not valid
        $data = '{"n":"0e0","u":"07b46a1c-1970-373d-8ae7-71929660cf98"}';
        $request = new Request([], $_POST, [], [], [], [], $data);
        $request->headers->set('Content-Type', 'application/json');
        $reActionsController = new ReActionsController();
        $setLike = $reActionsController->setLike($request);
        $this->assertJson($setLike->getContent());
        $this->assertEquals($setLike->getStatusCode(), 400);

        // check if if user is anonymous
        $data = '{"n":"757575","u":"OEluZnpRVWp4U1FyRE1Sb1IvYnA1RkVqYnc4SmNyRWp6WENYVk5MNzRycjdQRkVD"}';
        $request = new Request([], $_POST, [], [], [], [], $data);
        $request->headers->set('Content-Type', 'application/json');
        $reActionsController = new ReActionsController();
        $setLike = $reActionsController->setLike($request);
        $this->assertJson($setLike->getContent());
        $this->assertEquals($setLike->getStatusCode(), 403);

        // check if if uuid is invalid
        $data = '{"n":"757575","u":"Zy9rSUZITUJ5MW91MUVBeGI3SWZIK3NlRVB4dm56ZUZSNtFHb2FDalpRcUJ0c2Fo"}';
        $request = new Request([], $_POST, [], [], [], [], $data);
        $request->headers->set('Content-Type', 'application/json');
        $reActionsController = new ReActionsController();
        $setLike = $reActionsController->setLike($request);
        $this->assertJson($setLike->getContent());
        $this->assertEquals($setLike->getStatusCode(), 403);

        // check if if uuid is invalid
        $data = '{"n":"757575","u":"Zy9rSUZITUJ5MW91MUVBeGI3SWZIK3NlRVB4dm56ZUZSNtFHb2FDalpRcUJ0c2F"}';
        $request = new Request([], $_POST, [], [], [], [], $data);
        $request->headers->set('Content-Type', 'application/json');
        $reActionsController = new ReActionsController();
        $setLike = $reActionsController->setLike($request);
        $this->assertJson($setLike->getContent());
        $this->assertEquals($setLike->getStatusCode(), 400);


        // check if there is data in body and valid
        $data = '{"n":"757575","u":"Zy9rSUZITUJ5MW91MUVBeGI3SWZIK3NlRVB4dm56ZUZSNUFHb2FDalpRcUJ0c2Fo"}';
        $request = new Request([], $_POST, [], [], [], [], $data);
        $request->headers->set('Content-Type', 'application/json');
        $reActionsController = new ReActionsController();
        $setLike = $reActionsController->setLike($request);
        $this->assertJson($setLike->getContent());
        $this->assertEquals($setLike->getStatusCode(), 201);


        // check if there is  already a record
        $data = '{"n":"757575","u":"Zy9rSUZITUJ5MW91MUVBeGI3SWZIK3NlRVB4dm56ZUZSNUFHb2FDalpRcUJ0c2Fo"}';
        $request = new Request([], $_POST, [], [], [], [], $data);
        $request->headers->set('Content-Type', 'application/json');
        $reActionsController = new ReActionsController();
        $setLike = $reActionsController->setLike($request);
        $this->assertJson($setLike->getContent());
        $this->assertEquals($setLike->getStatusCode(), 202);


    }


    public function testSetDislike()
    {
        // check if there is no data in body
        $request = new Request([], $_POST, [], [], [], []);
        $request->headers->set('Content-Type', 'application/json');
        $reActionsController = new ReActionsController();
        $setLike = $reActionsController->setDislike($request);
        $this->assertJson($setLike->getContent());
        $this->assertEquals($setLike->getStatusCode(), 400);

        // check if there is data in body but no value
        $data = '{"n":"","u":""}';
        $request = new Request([], $_POST, [], [], [], [], $data);
        $request->headers->set('Content-Type', 'application/json');
        $reActionsController = new ReActionsController();
        $setLike = $reActionsController->setDislike($request);
        $this->assertJson($setLike->getContent());
        $this->assertEquals($setLike->getStatusCode(), 400);

        // check if if user is anonymous
        $data = '{"n":"757575","u":"OEluZnpRVWp4U1FyRE1Sb1IvYnA1RkVqYnc4SmNyRWp6WENYVk5MNzRycjdQRkVD"}';
        $request = new Request([], $_POST, [], [], [], [], $data);
        $request->headers->set('Content-Type', 'application/json');
        $reActionsController = new ReActionsController();
        $setLike = $reActionsController->setDislike($request);
        $this->assertJson($setLike->getContent());
        $this->assertEquals($setLike->getStatusCode(), 403);

        // check if if uuid is invalid
        $data = '{"n":"757575","u":"Zy9rSUZITUJ5MW91MUVBeGI3SWZIK3NlRVB4dm56ZUZSNUFHb2FDaltRcUJ0c2Fo"}';
        $request = new Request([], $_POST, [], [], [], [], $data);
        $request->headers->set('Content-Type', 'application/json');
        $reActionsController = new ReActionsController();
        $setLike = $reActionsController->setDislike($request);
        $this->assertJson($setLike->getContent());
        $this->assertEquals($setLike->getStatusCode(), 403);

        // check if if uuid is invalid
        $data = '{"n":"757575","u":"OEluZnpRVWp4U1FyRE1Sb1IvYnA1RkVqYnc4SmNyRWp6WENYVk5MNzRycjQRkVD"}';
        $request = new Request([], $_POST, [], [], [], [], $data);
        $request->headers->set('Content-Type', 'application/json');
        $reActionsController = new ReActionsController();
        $setLike = $reActionsController->setDislike($request);
        $this->assertJson($setLike->getContent());
        $this->assertEquals($setLike->getStatusCode(), 400);


        // check if there is data in body and valid
        $data = '{"n":"757575","u":"Zy9rSUZITUJ5MW91MUVBeGI3SWZIK3NlRVB4dm56ZUZSNUFHb2FDalpRcUJ0c2Fo"}';
        $request = new Request([], $_POST, [], [], [], [], $data);
        $request->headers->set('Content-Type', 'application/json');
        $reActionsController = new ReActionsController();
        $setLike = $reActionsController->setDislike($request);
        $this->assertJson($setLike->getContent());
        $this->assertEquals($setLike->getStatusCode(), 201);


        // check if there is  already a record
        $data = '{"n":"757575","u":"Zy9rSUZITUJ5MW91MUVBeGI3SWZIK3NlRVB4dm56ZUZSNUFHb2FDalpRcUJ0c2Fo"}';
        $request = new Request([], $_POST, [], [], [], [], $data);
        $request->headers->set('Content-Type', 'application/json');
        $reActionsController = new ReActionsController();
        $setLike = $reActionsController->setDislike($request);
        $this->assertJson($setLike->getContent());
        $this->assertEquals($setLike->getStatusCode(), 202);


    }

    public function testSetNewStatus()
    {

        $reActionsController = new ReActionsController();

        $setNewStatus = $reActionsController->setNewStatus(2, 2, 1);
        $this->assertEquals(1, $setNewStatus);

        $setNewStatus = $reActionsController->setNewStatus(1, 2, 1);
        $this->assertEquals(0, $setNewStatus);

        $setNewStatus = $reActionsController->setNewStatus(0, 2, 1);
        $this->assertEquals(1, $setNewStatus);

        $setNewStatus = $reActionsController->setNewStatus(2, 2, 1);
        $this->assertEquals(1, $setNewStatus);

        $setNewStatus = $reActionsController->setNewStatus(2, 1, 2);
        $this->assertEquals(0, $setNewStatus);

        $setNewStatus = $reActionsController->setNewStatus(1, 1, 2);
        $this->assertEquals(2, $setNewStatus);

        $setNewStatus = $reActionsController->setNewStatus(0, 1, 2);
        $this->assertEquals(2, $setNewStatus);

        $setNewStatus = $reActionsController->setNewStatus(1, 1, 2);
        $this->assertEquals(2, $setNewStatus);


    }

    public function testReturnDataInJson()
    {
        $returnDataInJson = (new Reusable())->returnDataInJson('test text', 'test message', 200, 'OK');
        $this->assertJson($returnDataInJson->getContent());
        $this->assertEquals($returnDataInJson->getStatusCode(), 200);


    }


    public function testDecrypt()
    {
        $decrypt = (new Reusable())->decrypt('OEluZnpRVWp4U1FyRE1Sb1IvYnA1RkVqYnc4SmNyRWp6WENYVk5MNzRycjdQRkVD', 'xxxxx', 'yyyyy');
        $this->assertEquals('f0483750-665f-43c9-b2ca-24e7d26f4049', $decrypt);
        $uuid = (New Validators())->uuidValidator(['uuid' => $decrypt]);
        $this->assertTrue($uuid);

        $decrypt = (new Reusable())->decrypt('OEluZnpRVWp4U1FyRE1Sb1IvYnA1RkVqYnc4SmNyRWp6WENYVk5MNzRycjdQRkV', 'xxxxx', 'yyyyy');
        $this->assertNotEquals('f0483750-665f-43c9-b2ca-24e7d26f4049', $decrypt);
        $uuid = (New Validators())->uuidValidator(['uuid' => $decrypt]);
        $this->assertFalse($uuid);

        $decrypt = (new Reusable())->decrypt('OEluZnpRVWp4U1FyRE1Sb1IvYnA1RkVqYnc4SmNyRWp6WENYVk5MNzRycjdQRkVD', 'xxx', 'yyyyy');
        $this->assertNotEquals('f0483750-665f-43c9-b2ca-24e7d26f4049', $decrypt);
        $uuid = (New Validators())->uuidValidator(['uuid' => $decrypt]);
        $this->assertFalse($uuid);

        $decrypt = (new Reusable())->decrypt('OEluZnpRVWp4U1FyRE1Sb1IvYnA1RkVqYnc4SmNyRWp6WENYVk5MNzRycjdQRkVD', 'xxxxx', 'yyy');
        $this->assertNotEquals('f0483750-665f-43c9-b2ca-24e7d26f4049', $decrypt);
        $uuid = (New Validators())->uuidValidator(['uuid' => $decrypt]);
        $this->assertFalse($uuid);

        $decrypt = (new Reusable())->decrypt('f0483750-665f-43c9-b2ca-24e7d26f4049', 'xxxxx', 'yyyyy');
        $this->assertNotEquals('f0483750-665f-43c9-b2ca-24e7d26f4049', $decrypt);
        $uuid = (New Validators())->uuidValidator(['uuid' => $decrypt]);
        $this->assertFalse($uuid);

    }

    public function testEncrypt()
    {
        $decrypt = (new Reusable())->encrypt('f0483750-665f-43c9-b2ca-24e7d26f4049', 'xxxxx', 'yyyyy');
        $this->assertIsString($decrypt);
        $this->assertEquals(64, strlen($decrypt));

        $decrypt = (new Reusable())->decrypt($decrypt, 'xxxxx', 'yyyyy');
        $this->assertEquals('f0483750-665f-43c9-b2ca-24e7d26f4049', $decrypt);
        $uuid = (New Validators())->uuidValidator(['uuid' => $decrypt]);
        $this->asserttrue($uuid);

    }


}
