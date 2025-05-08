<?php
require "vendor/autoload.php";
$config = require("config.php");

session_start();

// creating PDO object
Flight::register('db', 'PDO', array($config['dsn'], $config['user'], $config['pass']));
$db = Flight::db();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

Flight::before('start', function() use ($db) { // extracting username
    $path = Flight::request()->url;

    if (in_array($path, ['/login', '/register'])) {
        return;
    }

    $user = null;
    if (isset($_SESSION['user'])) {
        $stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->bindValue(':id', $_SESSION['user']);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        Flight::set('user', $user);
        Flight::set('username', $user ? $user['username'] : null);
    } else {
        Flight::redirect('/login');
        Flight::stop();
    }
});

Flight::route('GET /', function() { // rendering home page
    $username = Flight::get('username');
    $data = [
        'title' => 'Home Page',
        'username' => $username
    ];
    Flight::render('home', $data);
});

Flight::route('POST /', function () use ($db) { // user search logic
    $username = $_POST['username'];

    $stmt = $db->prepare('SELECT id FROM users WHERE username = :username');
    $stmt->bindValue(':username', $username);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['id']) {
        $id = $user['id'];
        Flight::redirect("/user/$id");
    }
    else {
        $data = [
            'title' => 'Home Page',
            'username' => Flight::get('username'),
            'error' => 'No user found'
        ];
        Flight::render('home', $data);
    }
});

Flight::route('/user/@id', function($id) use ($db) { // user's page
    $stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
    $stmt->bindValue(':id', $id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $data = [
        'title' => 'User' . $user['username'],
        'username' => $user['username'],
        'created_at' => $user['created_at'],
        'id' => $id
    ];
    Flight::render('user-page', $data);
});

Flight::route('GET /register', function() use ($db) { // rendering registration form
    $data = ['title' => 'Registration'];
    Flight::render('register', $data);
});

Flight::route('POST /register', function() use ($db) { // registration logi
    $username = $_POST['username'];
    $stmt = $db->prepare('SELECT username FROM users WHERE username = :username');
    $stmt->bindValue(':username', $username);
    $stmt->execute();
    $temp = $stmt->fetch(PDO::FETCH_ASSOC);
    if (empty($temp)) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $db->prepare('INSERT INTO users (username, password, created_at) VALUES (:username, :password, NOW())');
        $stmt->bindValue(':username', $username);
        $stmt->bindValue(':password', $password);
        $stmt->execute();

        Flight::redirect('/login');
    } else {
        $data = [
            'title' => 'Registration',
            'error' => 'The username is already taken'
        ];
        Flight::render('register', $data);
    }
});

Flight::route('GET /login', function() use ($db) { // rendering login form
    $data = ['title' => 'Login'];
    Flight::render('login', $data);
});

Flight::route('POST /login', function() use ($db) { // login logic
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $db->prepare('SELECT * FROM users WHERE username = :username');
    $stmt->bindValue(':username', $username);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user['id'];
        $id = $_SESSION['user'];
        Flight::redirect("/user/$id");
    } else {
        $data = [
            'title' => 'Login',
            'error' => 'Wrong username or password'
        ];
        Flight::render('login', $data);
    }
});

Flight::route('/logout', function () { // logout logic
    session_destroy();
    Flight::redirect('/login');
});

Flight::route('GET /notes', function () use ($db) { // notes page
    $user = Flight::get('user');
    if (!$user) return Flight::redirect('/login');

    $stmt = $db->prepare('SELECT * FROM notes WHERE user_id = :user_id ORDER BY created_at DESC');
    $stmt->bindValue(':user_id', $user['id']);
    $stmt->execute();

    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [
        'title' => 'Your Notes',
        'notes' => $notes,
        'username' => $user['username']
    ];
    Flight::render('notes-page', $data);
});


Flight::route('POST /notes', function() use ($db) { // notes logic
    $user = Flight::get('user');
    if (!$_SESSION['user']) return Flight::redirect('/login');

    $content = trim($_POST['content']);
    if ($content !== '') {
        $stmt = $db->prepare('INSERT INTO notes (user_id, content, created_at) VALUES (:user_id, :content, NOW())');
        $stmt->bindValue(':user_id', $user['id']);
        $stmt->bindValue(':content', $content);
        $stmt->execute();
    }

    Flight::redirect('/notes');
});

Flight::start();