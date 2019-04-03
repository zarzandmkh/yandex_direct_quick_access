Yandex direct quick access
A simple applcation for quckly one click getting your yandex direct account basic information as
 - number of groups
 - nubmer of phrases
 - status
 - groups with less hits
 - groups with less than 3 ads
 - groups with rejected status 
in one window.

1.register application in yandex oauth service https://oauth.yandex.ru/client/new slect web-service type and set 2 certain callback urls
 - http://your app domain/yandex_auth.php
 - http://your app domain/yandex_auth_register.php
(images/app_register_example.png)

2.add your app information to the  Yandex direct quick access application setting 
 model->app_id
 model->app_pass

3.then you need to create account in application which will be saved in db so you can access to it by one click
