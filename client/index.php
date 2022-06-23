<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js" integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13" crossorigin="anonymous"></script>
<?php

define('OAUTH_CLIENT_ID', '621f59c71bc35');
define('OAUTH_CLIENT_SECRET', '621f59c71bc36');
define('FACEBOOK_CLIENT_ID', '1154946705081703');
define('FACEBOOK_CLIENT_SECRET', '4e9835eeba3388b55bcb02b2cd27d522');
define('LINKEDIN_CLIENT_ID', '78ih5ts4jtua92');
define('LINKEDIN_CLIENT_SECRET','gTIo9xTOquRmcxx1');
define('GOOGLE_CLIENT_ID', '44229373992-45hc7opqdtd7p2iq1bjr1c8g5rga6s2r.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET','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');


function login()
{
    $queryParams= http_build_query([
        'client_id' => OAUTH_CLIENT_ID,
        'redirect_uri' => 'http://localhost:8081/callback',
        'response_type' => 'code',
        'scope' => 'basic',
        "state" => bin2hex(random_bytes(16))
    ]);
    echo "
        <form action='/callback' method='post'>
            <input type='text' name='username'/>
            <input type='password' name='password'/>
            <input type='submit' value='Login'/>
        </form>
    ";
    echo "<a class='btn btn-danger' href =\"http://localhost:8080/auth?{$queryParams}\">OauthServer</a>";

    $queryParams= http_build_query([
        'client_id' => FACEBOOK_CLIENT_ID,
        'redirect_uri' => 'http://localhost:8081/fb_callback',
        'response_type' => 'code',
        'scope' => 'public_profile,email',
        "state" => bin2hex(random_bytes(16))
    ]);
    echo "<a class='btn btn-danger'= href =\"https://www.facebook.com/v2.10/dialog/oauth?{$queryParams}\">Facebook</a>";

    $queryParams= http_build_query([
        'response_type' => 'code',
        'client_id' => LINKEDIN_CLIENT_ID,
        'redirect_uri' => 'http://localhost:8081/lkdn_callback',
        'state' => bin2hex(random_bytes(16)),
        'scope' => 'r_liteprofile r_emailaddress'
    ]);       
    // Design pattern de factory 
    // Class abstraite provider provider interface
    //echo "https://www.linkedin.com/oauth/v2/authorization?{$queryParams}";
    echo "<a class='btn btn-danger' href=\"https://www.linkedin.com/oauth/v2/authorization?{$queryParams}\">Linkedin</a>";

    $queryParams= http_build_query([
        'scope' => 'email',
        'response_type' => 'code',
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => 'http://localhost:8081/google_callback',
        'state' => bin2hex(random_bytes(16))
    ]);
        // 'response_type' => 'code',
       
    // Design pattern de factory 
    // Class abstraite provider provider interface
    //echo "https://www.linkedin.com/oauth/v2/authorization?{$queryParams}";
    echo "<a class='btn btn-danger' href=\"https://accounts.google.com/o/oauth2/v2/auth?{$queryParams}\">Google</a>";
    echo "<br>";
}

// Exchange code for token then get user info
function callback()
{
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        ["username" => $username, "password" => $password] = $_POST;
        $specifParams = [
            'username' => $username,
            'password' => $password,
            'grant_type' => 'password',
        ];
    } else {
        ["code" => $code, "state" => $state] = $_GET;

        $specifParams = [
            'code' => $code,
            'grant_type' => 'authorization_code',
        ];
    }

    $queryParams = http_build_query(array_merge([
        'client_id' => OAUTH_CLIENT_ID,
        'client_secret' => OAUTH_CLIENT_SECRET,
        'redirect_uri' => 'http://localhost:8081/callback',
    ], $specifParams));
    $response = file_get_contents("http://server:8080/token?{$queryParams}");
    $token = json_decode($response, true);
    
    $context = stream_context_create([
        'http' => [
            'header' => "Authorization: Bearer {$token['access_token']}"
            ]
        ]);
    $response = file_get_contents("http://server:8080/me", false, $context);
    $user = json_decode($response, true);
    echo "Hello {$user['lastname']} {$user['firstname']}";
}

function fbcallback()
{
    ["code" => $code, "state" => $state] = $_GET;

    $specifParams = [
            'code' => $code,
            'grant_type' => 'authorization_code',
        ];

    $queryParams = http_build_query(array_merge([
        'client_id' => FACEBOOK_CLIENT_ID,
        'client_secret' => FACEBOOK_CLIENT_SECRET,
        'redirect_uri' => 'http://localhost:8081/fb_callback',
    ], $specifParams));
    $response = file_get_contents("https://graph.facebook.com/v2.10/oauth/access_token?{$queryParams}");
    $token = json_decode($response, true);
    
    $context = stream_context_create([
        'http' => [
            'header' => "Authorization: Bearer {$token['access_token']}"
            ]
        ]);
    $response = file_get_contents("https://graph.facebook.com/v2.10/me", false, $context);
    $user = json_decode($response, true);
    echo "Hello {$user['name']}";
}
function lkdncallback(){
    ["code" => $code, "state" => $state] = $_GET;

    $specifParams = [
            'code' => $code,
            'grant_type' => 'authorization_code',
        ];

    $queryParams = http_build_query(array_merge([
        'client_id' =>LINKEDIN_CLIENT_ID,
        'client_secret' => LINKEDIN_CLIENT_SECRET,
        'redirect_uri' => 'http://localhost:8081/lkdn_callback',
    ], $specifParams));
    $response = file_get_contents("https://graph.facebook.com/v2.10/oauth/access_token?{$queryParams}");
    $token = json_decode($response, true);
    
    $context = stream_context_create([
        'http' => [
            'header' => "Authorization: Bearer {$token['access_token']}"
            ]
        ]);
    $response = file_get_contents("https://graph.facebook.com/v2.10/me", false, $context);
    $user = json_decode($response, true);
    echo "Hello {$user['name']}";
}
function googlecallback(){
    var_dump("GOOGLE CONNECTED");
    var_dump($_GET);

    ["code" => $code, "state" => $state] = $_GET;

    $specifParams = [
            'code' => $code,
            'grant_type' => 'authorization_code',
        ];

    $queryParams = http_build_query(array_merge([
        'client_id' =>GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => 'http://localhost:8081/google_callback',
    ], $specifParams));
    echo($queryParams);
    $response = file_get_contents("https://www.googleapis.com/oauth2/v1/userinfo?alt=json?{$queryParams}");
    $token = json_decode($response, true);
    var_dump($token);
    
    $context = stream_context_create([
        'http' => [
            'header' => "Authorization: Bearer {$token['access_token']}"
            ]
        ]);
    // $response = file_get_contents("https://graph.facebook.com/v2.10/me", false, $context);
    $user = json_decode($response, true);
    echo "Hello {$user['name']}";
}

$route = $_SERVER["REQUEST_URI"];
switch (strtok($route, "?")) {
    case '/login':
        login();
        break;
    case '/callback':
        callback();
        break;
    case '/fb_callback':
        fbcallback();
        break;
    case '/lkdn_callback':
        lkdncallback();
        break;
    case '/google_callback':
        googlecallback();
        break;
    default:
        http_response_code(404);
        break;
}