import app from 'flarum/app';

import WechatSettingsModal from 'ryccheen/auth/wechat/components/WechatSettingsModal';

app.initializers.add('ryccheen-auth-wechat', () => {
  app.extensionSettings['ryccheen-auth-wechat'] = () => app.modal.show(new WechatSettingsModal());
});
