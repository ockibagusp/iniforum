<?php namespace app\contollers;

use app\models\Accounts;
use app\models\Categories;
use app\models\Posts;
use Ngaji\Http\Request;
use Ngaji\Http\Response;
use Ngaji\Http\Session;
use Ngaji\Routing\Controller;
use Ngaji\FileHandler\File;
use Ngaji\view\View;

# use Response::render() func. to include template without passing array data
class MemberController extends Controller {

    public static function index() {
        $posts = Posts::all();
        $hotposts = Posts::all('ORDER BY viewers DESC');
        $users = Accounts::find([
            'type' => 2 # cause type 1 is admin
        ]);

        $categories = Categories::all();

        # /app/views/waitress/order.php
        View::render('home', [
            'hotposts' => $hotposts,
            'posts' => $posts,
            'users' => $users,
            'categories' => $categories
        ]);
    }

    /**
     * Render the profile page
     * @param $username
     */
    public static function profile($username) {
        # fetch account data by username
        $account = Accounts::findByUsername($username);
        # fecth all categories
        $categories = Categories::all();
        # fetch all of this member's post
        $posts = Posts::find([
            'id_account' => $account['id'],
        ]);

        # fetch all accounts
        $users = Accounts::find([
            'type' => 2 # cause type 1 is admin
        ]);

        View::render('member/profile', [
            'account' => $account,
            'posts' => $posts,
            'users' => $users,
            'categories' => $categories
        ]);
    }

    public static function edit() {
        # login required decorator
        self::login_required();

        # if user perform the form submit button
        if ("POST" == Request::method()) {

            $id = Request::user()->id;
            $name = Request::POST()->name;
            $username = Request::POST()->username;
            $bio = Request::POST()->bio;

            $profile_picture = File::upload('img', 'change_photo');

            if ($profile_picture)
                Accounts::edit($id, $name, $username, $bio, $profile_picture);
            else
                Accounts::edit($id, $name, $username, $bio);

            # push a flash message
            Session::push('flash-message', 'Your profile biodata has changed successfully!');

            # if username or name has changed
            # reconfigure the member session data 
            if ($name !== Request::user()->name or 
                $username !== Request::user()->username) {
                
                # get member data by id
                $data = Accounts::findByPK($id);

                # Set a session ID
                $account = array(
                    $data['id'],
                    $data['username'],
                    $data['name'],
                    $data['type']
                );

                $session = new Session();
                $session->set('id_account', implode('|', $account));
            }

            # redirect member profile page
            Response::redirect(
                'profile/' .
                Request::user()->username
            );
        } else {
            # redirect to home
            Response::redirect('');
        }
    }

    public static function register() {
        # if user was login before
        if (Request::is_authenticated())
            # redirect to main page
            Response::redirect('');

        if ("POST" == Request::method()) {

        } else {
            View::render('member/register');
        }
    }
}
