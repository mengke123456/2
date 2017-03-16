<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "shop_admin".
 *
 * @property integer $adminid
 * @property string $adminuser
 * @property string $adminpss
 * @property string $adminemail
 * @property integer $logintime
 * @property integer $loginip
 * @property integer $createtime
 */
class ShopAdmin extends \yii\db\ActiveRecord {

//为“记住我“创建一个新的方法,并且默认打开
    public $rememberMe = true;

//（gii）连接数据库中的表shop_admin
    public static function tableName() {
        return 'shop_admin';
    }

    //提交到数据库前执行简单的验证
    public function rules() {
        return [
            ['adminuser', 'required', 'message' => '管理员帐号不能为空'],
            ['adminpss', 'required', 'message' => '管理员密码不能为空'],
            ['rememberMe', 'boolean'],
            ['adminpss', 'validatePass'],
        ];
    }

    public function validatePass() {
        //判断之前是否有错误，再查询adminuser及adminpss
        if (!$this->hasErrors()) {

            $data = self::find()->where('adminuser= :user and adminpss = :pass', [
                        ":user" => $this->adminuser, ":pass" => md5($this->adminpss)
                    ])->one();

            //根据$data返回类型判断，为空报错
            if (is_null($data)) {
                $this->addError("adminpss", "用户名或密码错误");
            }
        }
    }

   
    public function login($data) {

        //如果载入成功并且验证成功
        if ($this->load($data) && $this->validate()) {

            //勾选‘记住我’则设置session有效期为lifetime）
            $session = Yii::$app->session;
            $lifetime = $this->rememberMe ? 24 * 3600 : 0;
            session_set_cookie_params($lifetime);

            //写入session
            $session['admin'] = ['adminuser' => $this->adminuser,
                'isLogin' => 1,];

            //更新登录时间/IP
            $this->updateAll(['logintime' => time(), 'loginip' => ip2long(Yii::$app->request->userIP)], 'adminuser= :user', [':user' => $this->adminuser]);

            //写入成功与否，返回 强转为bool 的类型
            return (bool) $session['admin']['isLogin'];
        }
        return FALSE;
    }

//（gii）数据表验证规则
//    public function rules() {
//        return [
//            [['adminuser', 'adminpss', 'adminemail', 'createtime'], 'required'],
//            [['logintime', 'loginip', 'createtime'], 'integer'],
//            [['adminuser', 'adminpss'], 'string', 'max' => 32],
//            [['adminemail'], 'string', 'max' => 50],
//            [['adminuser', 'adminpss'], 'unique', 'targetAttribute' => ['adminuser', 'adminpss'], 'message' => 'The combination of 管理员帐号 and 管理员密码 has already been taken.'],
//            [['adminuser', 'adminemail'], 'unique', 'targetAttribute' => ['adminuser', 'adminemail'], 'message' => 'The combination of 管理员帐号 and 管理员邮箱 has already been taken.'],
//        ];
//    }
//（gii）属性标签
    public function attributeLabels() {
        return [
            'adminid' => '主键ID',
            'adminuser' => '管理员帐号',
            'adminpss' => '管理员密码',
            'adminemail' => '管理员邮箱',
            'logintime' => '登录时间',
            'loginip' => '登录IP',
            'createtime' => '创建时间',
        ];
    }

}
