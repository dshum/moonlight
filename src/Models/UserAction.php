<?php namespace Moonlight\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;

class UserAction extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'admin_user_actions';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'action_type_id',
        'comments',
        'url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActionTypeName()
    {
        return UserActionType::getActionTypeName($this->action_type_id);
    }

    public static function log($actionTypeId, $comments, User $user = null)
    {
        $loggedUser = $user ?: Auth::guard('moonlight')->user();

        $method = $_SERVER['REQUEST_METHOD'] ?? null;
        $uri = $_SERVER['REQUEST_URI'] ?? null;

        self::create([
            'user_id' => $loggedUser->id,
            'action_type_id' => $actionTypeId,
            'comments' => $comments,
            'url' => $method.' '.$uri,
        ]);
    }
}
