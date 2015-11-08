<?php
/**
 * To make IDEs autocomplete happy
 *
 * @property int id
 * @property string login
 * @property bool active
 * @property string customerId
 * @property string firstName
 * @property string lastName
 * @property string password
 * @property string createdAt
 * @property string updatedAt
 * @property string expires
 * @property int loginCount
 */
class user extends Model {
    protected $dbTable = "users";
    protected $dbFields = Array (
        'role' => Array ('text'),
        'email' => Array ('text'),
        'password' => Array ('text'),
        'lastlogindate' => Array ('datetime'),
        'lastloginip' => Array ('text'),
        'createdAt' => Array ('datetime'),
        'updatedAt' => Array ('datetime')
    );

    protected $timestamps = Array ('createdAt', 'updatedAt');

    public static function login ($email, $password) {
        $user = user::where ('email', $email)
                    ->where ('password', sha1 (TinyMvc::app()->config['salt'] . $password))
                    ->getOne ();
        if (!$user)
            return false;
        $user->lastlogindate = date("Y-m-d H:i:s");
        $user->lastloginip = $_SERVER['REMOTE_ADDR'];
        $user->save();
        return $user;
    }
}
?>
