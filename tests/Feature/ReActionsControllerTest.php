<?php
/**
 * Created by PhpStorm.
 * User: Django
 * Date: 1/11/2020
 * Time: 4:51 PM
 */

namespace App\Http\Controllers\test\Feature;


use App\Http\Controllers\helper\Reusable;
use App\Http\Controllers\ReActionsController;
use App\ReActions;
use App\Total;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ReActionsControllerTest extends TestCase
{
    const VOTES = '_api/_v1/votes';
    private $reActions;
    private $reActionsFirst;
    private $total;
    private $totalFirst;

    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:fresh --seed');
        $this->reActions = new ReActions();
        $this->reActionsFirst = $this->reActions->all()->first();
        $this->total = new  Total();
        $this->totalFirst = $this->total->all()->first();
    }

    public function testGetDataApi()
    {
        // check if no param
        $url = self::VOTES;
        $response = $this->get($url);
        $response->assertStatus(404);

        // check if no nid value
        $url = self::VOTES . '?u=' . $this->reActionsFirst->uuid . '';
        $response = $this->get($url);
        $response->assertStatus(404);

        // check if no  value
        $url = self::VOTES . '?n=&u=' . $this->reActionsFirst->uuid . '';
        $response = $this->get($url);
        $response->assertStatus(404);

        // check if no  uuid value and nid not number
        $url = self::VOTES . '?n=0e0&u=' . $this->reActionsFirst->uuid . '';
        $response = $this->get($url);
        $response->assertStatus(404);

        // check if has value and invalid
        $url = self::VOTES . '?n=' . $this->reActionsFirst->nid . '&u=sadsadasdas';
        $response = $this->get($url);
        $response->assertStatus(403);

        // check if has value and invalid
        $url = self::VOTES . '?n=' . $this->reActionsFirst->nid . '&u=Zy9rSUZITUJ5MW91MUVBeGI3SWaIK3NlRVB4dm56ZUZSNUFHb2FDalpRcUJ0c2Fo';
        $response = $this->get($url);
        $response->assertStatus(403);

        $url = self::VOTES . '?n=' . $this->reActionsFirst->nid . '&u=' . (new Reusable())->encrypt($this->reActionsFirst->uuid, 'FK2nCxJopiriQ', env('DECRYPT_IV')) . '';
        $response = $this->get($url);
        $response->assertStatus(403);

        $url = self::VOTES . '?n=' . $this->reActionsFirst->nid . '&u=' . (new Reusable())->encrypt($this->reActionsFirst->uuid, env('DECRYPT_KEY'), env('DECRYPT_IV')) . '';
        $response = $this->get($url);
        $response->assertStatus(200);

    }

    public function testPostListApi()
    {
        // check if array hs no object
        $data = [];
        $url = self::VOTES . '/list';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(400);

        // check if array size lower than 1
        $data = [
            "list" => '',
        ];
        $url = self::VOTES . '/list';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(400);

        // check if data not array
        $data = [
            "list" => 222222,
        ];
        $url = self::VOTES . '/list';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(400);

        // check if array not valid
        $data = [
            "list" => ['dsfdsf'],
        ];
        $url = self::VOTES . '/list';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(400);

        // check if array size bigger than 20
        $data = [
            "list" => [222222, 222222, 222222, 222222, 222222, 222222, 222222, 222222, 222222, 222222, 222222, 222222,
                222222, 222222, 222222, 222222, 222222, 222222, 222222, 222222, 222222,],
        ];
        $url = self::VOTES . '/list';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(400);

        // check if array is valid
        $data = [
            "list" => [$this->reActionsFirst->nid,3333],
        ];
        $url = self::VOTES . '/list';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(200);
        $this->assertJsonStringEqualsJsonString('{ "body": { "'.$this->reActionsFirst->nid.'": { "like": ' . $this->totalFirst->like . ', "dislike": ' . $this->totalFirst->dislike . ', "total": ' . $this->totalFirst->total . ', "wilson": ' . $this->totalFirst->wilson . ' } }, "message": null }', $response->getContent());

        $data = [
            "list" => [$this->reActionsFirst->nid,$this->reActionsFirst->nid],
        ];
        $url = self::VOTES . '/list';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(200);


    }

    public function testPostLikeApi()
    {

        // check if no data
        $data = [
        ];
        $url = self::VOTES . '/like';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(400);

        // route is invalid
        $data = [
            "n" => 222222,
            "u" => ""
        ];
        $url = self::VOTES . '/likes';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(404);

        $this->assertDatabaseMissing('re_actions', [
            'nid' => 222222
        ]);

        $this->assertDatabaseMissing('totals', [
            'nid' => 222222
        ]);


        // check if nid is invalid
        $data = [
            "n" => "0e0",
            "u" => "Zy9rSUZITUJ5MW91MUVBeGI3SWZIK3NlRVB4dm56ZUZSNUFHb2FDalpRcUJ0c2Fo"
        ];
        $url = self::VOTES . '/like';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(400);

        $this->assertDatabaseMissing('re_actions', [
            'nid' => '0e0'
        ]);

        $this->assertDatabaseMissing('totals', [
            'nid' => '0e0'
        ]);

        // check if no uuid
        $data = [
            "n" => 111111,
            "u" => ""
        ];
        $url = self::VOTES . '/like';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(400);

        $this->assertDatabaseMissing('re_actions', [
            'nid' => 111111
        ]);

        $this->assertDatabaseMissing('totals', [
            'nid' => 111111
        ]);


        //if user is anonymous
        $data = [
            "n" => 111111,
            "u" => "MTYwUFQzbFNtMTZFQnpQL2RyZkpOeHNNTVZKYzZoaisrNVVIdmFuclRZcEJDKzNC"
        ];
        $url = self::VOTES . '/like';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(403);

        $this->assertDatabaseMissing('re_actions', [
            'nid' => 111111,
        ]);

        $this->assertDatabaseMissing('totals', [
            'nid' => 111111,
        ]);


        //like
        $data = [
            "n" => 111111,
            "u" => "Zy9rSUZITUJ5MW91MUVBeGI3SWZIK3NlRVB4dm56ZUZSNUFHb2FDalpRcUJ0c2Fo"
        ];
        $url = self::VOTES . '/like';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(201);

        $this->assertDatabaseHas('re_actions', [
            'uuid' => '2d3c9de4-3831-4988-8afb-710fda2e740c',
            'nid' => 111111,
            'status' => 1,
            'change_number' => 1
        ]);

        $this->assertDatabaseHas('totals', [
            'nid' => 111111,
            'like' => 1,
            'dislike' => 0,
            'total' => 1,
            'wilson' => 0.2065
        ]);


        // like back
        $data = [
            "n" => 111111,
            "u" => "Zy9rSUZITUJ5MW91MUVBeGI3SWZIK3NlRVB4dm56ZUZSNUFHb2FDalpRcUJ0c2Fo"
        ];
        $url = self::VOTES . '/like';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(202);

        $this->assertDatabaseHas('re_actions', [
            'uuid' => '2d3c9de4-3831-4988-8afb-710fda2e740c',
            'nid' => 111111,
            'status' => 0,
            'change_number' => 2
        ]);

        $this->assertDatabaseHas('totals', [
            'nid' => 111111,
            'like' => 0,
            'dislike' => 0,
            'total' => 0,
            'wilson' => 0.0000
        ]);

        // like and dislike

        $data = [
            "n" => 111111,
            "u" => "Zy9rSUZITUJ5MW91MUVBeGI3SWZIK3NlRVB4dm56ZUZSNUFHb2FDalpRcUJ0c2Fo"
        ];
        $url = self::VOTES . '/like';
        $this->post($url, $data, ['Content-Type', 'application/json']);

        $url = self::VOTES . '/dislike';
        $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(202);

        $this->assertDatabaseHas('re_actions', [
            'uuid' => '2d3c9de4-3831-4988-8afb-710fda2e740c',
            'nid' => 111111,
            'status' => 2,
            'change_number' => 4
        ]);

        $this->assertDatabaseHas('totals', [
            'nid' => 111111,
            'like' => 0,
            'dislike' => 1,
            'total' => 1,
            'wilson' => 0.0000
        ]);

        // like after dislike
        $data = [
            "n" => 111111,
            "u" => "Zy9rSUZITUJ5MW91MUVBeGI3SWZIK3NlRVB4dm56ZUZSNUFHb2FDalpRcUJ0c2Fo"
        ];
        $url = self::VOTES . '/like';
        $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(202);

        $this->assertDatabaseHas('re_actions', [
            'uuid' => '2d3c9de4-3831-4988-8afb-710fda2e740c',
            'nid' => 111111,
            'status' => 1,
            'change_number' => 5
        ]);

        $this->assertDatabaseHas('totals', [
            'nid' => 111111,
            'like' => 1,
            'dislike' => 0,
            'total' => 1,
            'wilson' => 0.2065
        ]);

        // to many request
        $this->post($url, $data, ['Content-Type', 'application/json']);
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);

        $response->assertStatus(429);
        $this->assertDatabaseHas('re_actions', [
            'uuid' => '2d3c9de4-3831-4988-8afb-710fda2e740c',
            'nid' => 111111,
            'status' => 1,
            'change_number' => 5
        ]);

    }

    public function testPostDisLikeApi()
    {
        $data = [
            "n" => 222222,
            "u" => ""
        ];
        $url = self::VOTES . '/dislikes';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(404);

        // check if no data
        $data = [
        ];
        $url = self::VOTES . '/dislike';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(400);

        $this->assertDatabaseMissing('re_actions', [
            'nid' => 222222,
        ]);

        $this->assertDatabaseMissing('totals', [
            'nid' => 222222,
        ]);

        // check if nid is invalid
        $data = [
            "n" => "0e0",
            "u" => "Zy9rSUZITUJ5MW91MUVBeGI3SWZIK3NlRVB4dm56ZUZSNUFHb2FDalpRcUJ0c2Fo"
        ];
        $url = self::VOTES . '/dislike';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(400);

        $this->assertDatabaseMissing('re_actions', [
            'nid' => '0e0'
        ]);

        $this->assertDatabaseMissing('totals', [
            'nid' => '0e0'
        ]);

        // check if no uuid
        $data = [
            "n" => 222222,
            "u" => ""
        ];
        $url = self::VOTES . '/dislike';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(400);

        $this->assertDatabaseMissing('re_actions', [
            'nid' => 222222,
        ]);

        $this->assertDatabaseMissing('totals', [
            'nid' => 222222,
        ]);

        //if user is anonymous
        $data = [
            "n" => 111111,
            "u" => "MTYwUFQzbFNtMTZFQnpQL2RyZkpOeHNNTVZKYzZoaisrNVVIdmFuclRZcEJDKzNC"
        ];
        $url = self::VOTES . '/dislike';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(403);

        $this->assertDatabaseMissing('re_actions', [
            'nid' => 111111
        ]);

        $this->assertDatabaseMissing('totals', [
            'nid' => 111111
        ]);

        //dislike
        $data = [
            "n" => 222222,
            "u" => "Zy9rSUZITUJ5MW91MUVBeGI3SWZIK3NlRVB4dm56ZUZSNUFHb2FDalpRcUJ0c2Fo"
        ];
        $url = self::VOTES . '/dislike';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(201);

        $this->assertDatabaseHas('re_actions', [
            'uuid' => '2d3c9de4-3831-4988-8afb-710fda2e740c',
            'nid' => 222222,
            'status' => 2,
            'change_number' => 1
        ]);

        $this->assertDatabaseHas('totals', [
            'nid' => 222222,
            'like' => 0,
            'dislike' => 1,
            'total' => 1,
            'wilson' => 0.0000
        ]);


        // like back
        $data = [
            "n" => 222222,
            "u" => "Zy9rSUZITUJ5MW91MUVBeGI3SWZIK3NlRVB4dm56ZUZSNUFHb2FDalpRcUJ0c2Fo"
        ];
        $url = self::VOTES . '/dislike';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(202);

        $this->assertDatabaseHas('re_actions', [
            'uuid' => '2d3c9de4-3831-4988-8afb-710fda2e740c',
            'nid' => 222222,
            'status' => 0,
            'change_number' => 2
        ]);

        $this->assertDatabaseHas('totals', [
            'nid' => 222222,
            'like' => 0,
            'dislike' => 0,
            'total' => 0,
            'wilson' => 0.0000
        ]);


        $data = [
            "n" => 333333,
            "u" => "Zy9rSUZITUJ5MW91MUVBeGI3SWZIK3NlRVB4dm56ZUZSNUFHb2FDalpRcUJ0c2Fo"
        ];
        $url = self::VOTES . '/dislike';
        $this->post($url, $data, ['Content-Type', 'application/json']);
        $this->post($url, $data, ['Content-Type', 'application/json']);
        $this->post($url, $data, ['Content-Type', 'application/json']);
        $this->post($url, $data, ['Content-Type', 'application/json']);
        $this->post($url, $data, ['Content-Type', 'application/json']);
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);

        $response->assertStatus(429);

        $this->assertDatabaseHas('re_actions', [
            'uuid' => '2d3c9de4-3831-4988-8afb-710fda2e740c',
            'nid' => 333333,
            'status' => 2,
            'change_number' => 5
        ]);

    }

    public function testWilson()
    {
        // five likes
        $data = [
            "n" => 552255,
            "u" => $decrypt = (new Reusable())->encrypt('a5f85647-9521-4370-ba5b-c2191e9ae528', env('DECRYPT_KEY'), env('DECRYPT_IV')),
        ];
        $url = self::VOTES . '/like';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(201);

        $data = [
            "n" => 552255,
            "u" => $decrypt = (new Reusable())->encrypt('2d3c9de4-3831-4988-8afb-710fda2e740c', env('DECRYPT_KEY'), env('DECRYPT_IV')),
        ];
        $url = self::VOTES . '/like';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(201);

        $data = [
            "n" => 552255,
            "u" => $decrypt = (new Reusable())->encrypt('0a5ceac9-da9c-431d-835f-af10d1216ba9', env('DECRYPT_KEY'), env('DECRYPT_IV')),
        ];
        $url = self::VOTES . '/like';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(201);

        $data = [
            "n" => 552255,
            "u" => $decrypt = (new Reusable())->encrypt('7fc0c38b-8ba6-4c24-8525-077d5b1dd253', env('DECRYPT_KEY'), env('DECRYPT_IV')),
        ];
        $url = self::VOTES . '/like';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(201);

        $data = [
            "n" => 552255,
            "u" => $decrypt = (new Reusable())->encrypt('c8513130-a7e5-4ba6-8891-0eab4984696d', env('DECRYPT_KEY'), env('DECRYPT_IV')),
        ];
        $url = self::VOTES . '/like';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(201);

        // 5 dislikes
        $data = [
            "n" => 552255,
            "u" => $decrypt = (new Reusable())->encrypt('9a0c93f9-74c4-4806-9046-8da4628c5a15', env('DECRYPT_KEY'), env('DECRYPT_IV')),
        ];
        $url = self::VOTES . '/dislike';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(201);

        $data = [
            "n" => 552255,
            "u" => $decrypt = (new Reusable())->encrypt('9467463c-903a-4190-a75d-0e1a9acc4876', env('DECRYPT_KEY'), env('DECRYPT_IV')),
        ];
        $url = self::VOTES . '/dislike';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(201);

        $data = [
            "n" => 552255,
            "u" => $decrypt = (new Reusable())->encrypt('287b3360-7bbd-4932-a7ad-71dfe865b219', env('DECRYPT_KEY'), env('DECRYPT_IV')),
        ];
        $url = self::VOTES . '/dislike';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(201);

        $data = [
            "n" => 552255,
            "u" => $decrypt = (new Reusable())->encrypt('25848d32-3732-4514-b7da-edc47cfcfb41', env('DECRYPT_KEY'), env('DECRYPT_IV')),
        ];
        $url = self::VOTES . '/dislike';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(201);

        $data = [
            "n" => 552255,
            "u" => $decrypt = (new Reusable())->encrypt('c6a7b1f8-8864-462f-b328-e9fe9d23ec18', env('DECRYPT_KEY'), env('DECRYPT_IV')),
        ];
        $url = self::VOTES . '/dislike';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(201);


        $this->assertDatabaseHas('totals', [
            'nid' => 552255,
            'like' => 5,
            'dislike' => 5,
            'total' => 10,
            'wilson' => 0.2366
        ]);
    }





}
