<?php

namespace Engelsystem\Controllers\Admin;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\BaseController;
use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\User\User;
use Psr\Log\LoggerInterface;

class UserShirtController extends BaseController
{
    use HasUserNotifications;

    protected Authenticator $auth;

    protected Config $config;

    protected LoggerInterface $log;

    protected Redirector $redirect;

    protected Response $response;

    protected User $user;

    /** @var array */
    protected array $permissions = [
        'editShirt' => 'user.edit.shirt',
        'saveShirt' => 'user.edit.shirt',
    ];

    public function __construct(
        Authenticator $auth,
        Config $config,
        LoggerInterface $log,
        Redirector $redirector,
        Response $response,
        User $user
    ) {
        $this->auth = $auth;
        $this->config = $config;
        $this->log = $log;
        $this->redirect = $redirector;
        $this->response = $response;
        $this->user = $user;
    }

    public function editShirt(Request $request): Response
    {
        $userId = (int)$request->getAttribute('user_id');

        $user = $this->user->findOrFail($userId);

        return $this->response->withView(
            'admin/user/edit-shirt.twig',
            ['userdata' => $user] + $this->getNotifications()
        );
    }

    public function saveShirt(Request $request): Response
    {
        $userId = (int)$request->getAttribute('user_id');

        /** @var User $user */
        $user = $this->user->findOrFail($userId);

        $data = $this->validate($request, [
            'shirt_size' => 'required',
            'arrived'    => 'optional|checked',
            'active'     => 'optional|checked',
            'got_shirt'  => 'optional|checked',
        ]);

        if (isset($this->config->get('tshirt_sizes')[$data['shirt_size']])) {
            $user->personalData->shirt_size = $data['shirt_size'];
            $user->personalData->save();
        }

        if ($this->auth->can('admin_arrive')) {
            $user->state->arrived = (bool)$data['arrived'];
        }

        $user->state->active = (bool)$data['active'];
        $user->state->got_shirt = (bool)$data['got_shirt'];
        $user->state->save();

        $this->log->info(
            'Updated user shirt state "{user}" ({id}): '
            . '{size}, arrived: {arrived}, active: {active}, got shirt: {got_shirt}',
            [
                'id'        => $user->id,
                'user'      => $user->name,
                'size'      => $user->personalData->shirt_size,
                'arrived'   => $user->state->arrived ? 'yes' : 'no',
                'active'    => $user->state->active ? 'yes' : 'no',
                'got_shirt' => $user->state->got_shirt ? 'yes' : 'no'
            ]
        );

        $this->addNotification('user.edit.success');

        return $this->redirect->back();
    }
}
