# yandex_direct_quick_access

A simple applcation for quckly one click getting your yandex direct account basic information as <br />
 - number of groups <br />
 - nubmer of phrases <br />
 - status <br />
 - groups with less hits <br />
 - groups with less than 3 ads <br />
 - groups with rejected status  <br />
in one window. <br />
 <br />
1.register application in yandex oauth service https://oauth.yandex.ru/client/new and set 2 certain callback urls <br />
 - http://your app domain/yandex_auth.php <br />
 - http://your app domain/yandex_auth_register.php <br />
(images/app_register_example.png) <br />
 <br />
2.add your app information to the  Yandex direct quick access application setting  <br />
 model->app_id <br />
 model->app_pass <br />
 <br />
3.then you need to create account in application which will be saved in db so you can access to it by one click <br />
