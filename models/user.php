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
        'email' => Array ('text'),
        'password' => Array ('text'),
        'lastlogindate' => Array ('datetime'),
        'lastloginip' => Array ('text'),
        'createdAt' => Array ('datetime'),
        'updatedAt' => Array ('datetime')
    );

    protected $timestamps = Array ('createdAt', 'updatedAt');
}
?>
