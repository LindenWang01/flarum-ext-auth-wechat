<?php
/*
 * This file is part of Flarum.
 *
 * (c) RycCheen <xls9009@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RycCheen\Auth\Wechat;

use Flarum\Core\User;
use Flarum\Forum\AuthenticationResponseFactory;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\Http\Controller\ControllerInterface;
use RycCheen\OAuth2\Client\Provider\Wechat;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Response\HtmlResponse;

class WechatBindController implements ControllerInterface
{
    /**
     * @var \League\OAuth2\Client\Provider\AbstractProvider
     */
    protected $provider;

    /**
     * The access token, once obtained.
     *
     * @var string
     */
    protected $token;

    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @param SettingsRepositoryInterface $settings
     */
    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    /**
     * {@inheritdoc}
     */
    protected function getProvider($redirectUri)
    {
        return new Wechat([
            'appid'        => $this->settings->get('ryccheen-auth-wechat.app_id'),
            'secret'       => $this->settings->get('ryccheen-auth-wechat.app_secret'),
            'redirect_uri' => $redirectUri
        ]);
    }

    /**
     * {@inheritdoc}
     * scope: 应用授权作用域，拥有多个作用域用逗号（,）分隔，网页应用目前仅填写snsapi_login
     */
    protected function getAuthorizationUrlOptions()
    {
        return [
            'scope' => 'snsapi_login'
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getIdentification($userId)
    {
        return [
            'id' => $userId
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getSuggestions(ResourceOwnerInterface $resourceOwner)
    {
        return [
            'username' => $resourceOwner->getUnionId(),
            'avatarUrl' => $resourceOwner->getHeadImgUrl()
        ];
    }

    /**
     * @param Request $request
     * @return \Psr\Http\Message\ResponseInterface|RedirectResponse
     */
    public function handle(Request $request)
    {
        $actor = $request->getAttribute('actor');
        $userId = $actor->id;

        $redirectUri = (string) $request->getAttribute('originalUri', $request->getUri())->withQuery('');

        $this->provider = $this->getProvider($redirectUri);

        $session = $request->getAttribute('session');

        $user = User::where(['id' => $userId])->first();

        if ($user->wechat_id != "") {
            $response = $this->rejectResponse();

            return $response;
        }

        $queryParams = $request->getQueryParams();
        $code = array_get($queryParams, 'code');
        $state = array_get($queryParams, 'state');

        if (! $code) {
            $authUrl = $this->provider->getAuthorizationUrl($this->getAuthorizationUrlOptions());
            $session->set('oauth2state', $this->provider->getState());

            return new RedirectResponse($authUrl.'&display=popup');
        } elseif (! $state || $state !== $session->get('oauth2state')) {
            $session->forget('oauth2state');
            echo 'Invalid state. Please close the window and try again.';
            exit;
        }

        $this->token = $this->provider->getAccessToken('authorization_code', compact('code'));

        $owner = $this->provider->getResourceOwner($this->token);

        $identification = $this->getIdentification($userId);
        $suggestions = $this->getSuggestions($owner);

        return $this->make($identification, $owner->getUnionId());
    }

    public function make(array $identification, $wechatId)
    {
        User::where($identification)->update(['wechat_id' => $wechatId]);

        $payload = ['authenticated' => true];

        $response = $this->getResponse($payload);

        return $response;
    }

    /**
     * @param array $payload
     * @return HtmlResponse
     */
    private function getResponse(array $payload)
    {
        $content = sprintf(
            '<script>window.opener.app.authenticationComplete(%s); window.close();</script>',
            json_encode($payload)
        );

        return new HtmlResponse($content);
    }

    /**
     * @return HtmlResponse
     */
    private function rejectResponse()
    {
        $content = sprintf(
           '<h2 style="background:yellow;text-align:center">You had already bind the wechat account!</h2>'
        );

        return new HtmlResponse($content);
    }
}
