<?php

/**
 * Plugin Name: PSNGameList
 * Plugin URI: https://www.azimiao.com
 * Description: 一个WP用的PSN游戏库列表
 * Version: 1.0.2
 * Author: 野兔#梓喵出没
 * Author URI: https://www.azimiao.com
 */


class Azimiao_PSN_List
{
    private $plugin_version = "v1.0.2";

    /**==================Options=================== **/

    private $optionName = "azimiao_psn_list";


    //基础参数
    private $api_base = 'api_base';

    private $api_limit = "api_limit";

    private $list_padding = "list_padding";

    private $card_width = "card_width";

    private $ajax_fix = "ajax_fix";

    private $auto_runjs = "auto_runjs";
    function __construct()
    {
        //创建菜单
        add_action("admin_menu", array($this, "initAdminPage"));
        //注册短代码
        add_action('init', array($this, "register_shortcodes"));
        //注册ajax方法
        add_action("wp_ajax_nopriv_GetGameListShowConfig", array($this, "GetGameListShowConfig"));
        add_action("wp_ajax_GetGameListShowConfig", array($this, "GetGameListShowConfig"));

        $options = $this->getOption();
        if(boolval($options[$this->ajax_fix])){
            add_action( 'wp_enqueue_scripts', array($this,'RegNeedScripts'));
        }
        
    }

    //获取存储的配置信息
    function getOption()
    {
        $options = get_option($this->optionName);
        if (!is_array($options)) {
            $options[$this->api_base] = admin_url('admin-ajax.php');

            $options[$this->api_limit] = 4;
            $options[$this->list_padding] = 10;
            $options[$this->ajax_fix] = false;
            $options[$this->card_width] = 260;
            $options[$this->auto_runjs] = true;
            update_option($this->optionName, $options);
        }
        if(!isset($options[$this->auto_runjs])){
            $options[$this->auto_runjs] = true;
            update_option($this->optionName, $options);
        }
        return $options;
    }

    function GetGameListShowConfig(){
        header("content-type:application/json");

        $options = $this->getOption();
        ?>
        {
            "apibase":"<?php echo $options[$this->api_base] ?>",
            "limit":<?php echo intval($options[$this->api_limit])?>,
            "padding":<?php echo intval($options[$this->list_padding])?>,
            "loadingImg":"<?php echo strval(plugins_url('assets/image/loading.gif', __FILE__)); ?>"
        }
        <?php
        die();
    }
    //如果必要，则注册全局脚本
    public function RegNeedScripts(){

        wp_register_script( 'psngamelist_zm_functiongridjs',  plugins_url('assets/js/minigrid.min.js', __FILE__));
        wp_enqueue_script( 'psngamelist_zm_functiongridjs' );//grid

        wp_register_script( 'psngamelist_zm_functionjs',  plugins_url('assets/js/azimiao_psn_gamelist.js', __FILE__));
        wp_enqueue_script( 'psngamelist_zm_functionjs' );//func



        wp_register_style( 'psngamelist_zm_css', plugins_url('assets/css/azimiao_psn_gamelist.css', __FILE__));
        wp_enqueue_style( 'psngamelist_zm_css' );
    }



    public function register_shortcodes()
    {
        add_shortcode('azimiao_psn_gamelist', array($this, 'output_gamelist'));
    }

    ///输出前台信息

    public function output_gamelist(){
        $options = $this->getOption();
        if(!boolval($options[$this->ajax_fix])){
            echo "<script src='" . plugins_url('assets/js/minigrid.min.js', __FILE__) . " '></script>";
            echo "<script src='" . plugins_url('assets/js/azimiao_psn_gamelist.js', __FILE__) . " '></script>";
            echo '<link rel="stylesheet" type="text/css" href="' . plugins_url('assets/css/azimiao_psn_gamelist.css', __FILE__) . ' " />';
        }
        
        $configUrl = admin_url('admin-ajax.php');
        $cardWidth = $options[$this->card_width];
        return "
        <style>
            .PsnItem{
                width:{$cardWidth}px
            }
            :root{
                --psnCardWidth:{$cardWidth}px
        </style>
        <div class='PsnGames' id='azimiao_psngames'>
                <div id='PsnItemContainer' class='PsnItemContainer'>
                </div>
                <br style='clear: both;'>   
                <div class='PsnButtons'>
                    <button id='btnMore' onclick='GetTrophyTitles()'>
                        加载更多
                    </button>
                </div>
        </div>" . (boolval($options[$this->auto_runjs]) ? "<script>PsnGameListConfigInit('{$configUrl}');</script>" : "");

    }


   

    private $nonceflag = "azimiao_psn_gamelist";
    private $nonceflagName = "azimiao_psn_gamelist_save";

    function initAdminPage()
    {
        $NonceFlagTest = false;
        $options = $this->getOption();

        add_options_page("PSN 游戏列表设置", "PSN 游戏列表设置", "manage_options", "azimiao_psn_gamelist_setting", array($this, "optionPage"));


        //TODO:处理提交内容
        if (isset($_POST[$this->nonceflagName])) {
            $Nonce = $_POST[$this->nonceflagName];

            if (wp_verify_nonce($Nonce, $this->nonceflag)) {
                $NonceFlagTest = true;
            }
        }

        $checkFlag1 = is_admin() && isset($_POST['psn-gamelist-save']);
        
        if ($NonceFlagTest) {
            //check Successful,do save and exec
            if($checkFlag1){
                if(isset($_POST[$this->api_base])){
                    if("" == strval($_POST[$this->api_base])){
                        $options[$this->api_base] = admin_url('admin-ajax.php');
                    }else{
                        $options[$this->api_base] =  strval($_POST[$this->api_base]);
                    }
                }else{
                    $options[$this->api_base] = admin_url('admin-ajax.php');
                }
                // $options[$this->api_base] = strval($_POST[$this->api_base] ??  admin_url('admin-ajax.php'));
                $options[$this->api_limit] = intval($_POST[$this->api_limit] ?? 4);
                $options[$this->list_padding] = intval($_POST[$this->list_padding] ?? 10);
                $options[$this->ajax_fix] = boolval($_POST[$this->ajax_fix] ?? false);
                $options[$this->card_width] = intval($_POST[$this->card_width] ?? 260);
                $options[$this->auto_runjs] = boolval($_POST[$this->auto_runjs] ?? false);
                if($options[$this->api_limit] <=0){
                    $options[$this->api_limit] = 4;
                }
                if($options[$this->card_width] <=0){
                    $options[$this->card_width] = 260;
                }

                update_option($this->optionName, $options);

                echo "<div id='message' class='updated fade'><p><strong>数据已更新</strong></p></div>";
            }
        } 
    }


    //输出后台页面
    function optionPage()
    {
        $options = $this->getOption();
?>
        <style type="text/css">
            #pure_form {
                font-family: "Century Gothic", "Segoe UI", Arial, "Microsoft YaHei", Sans-Serif;
            }

            .wrap {
                padding: 10px;
                font-size: 12px;
                line-height: 24px;
                color: #383838;
            }

            .otakutable td {
                vertical-align: top;
                text-align: left;
                border: none;
                font-size: 12px;
            }

            .top td {
                vertical-align: middle;
                text-align: left;
                border: none;
                font-size: 12px;
            }

            table {
                border: none;
                font-size: 12px;
            }

            pre {
                white-space: pre;
                overflow: auto;
                padding: 0px;
                line-height: 19px;
                font-size: 12px;
                color: #898989;
            }

            strong {
                color: #666
            }

            .none {
                display: none;
            }

            fieldset {
                width: 800px;
                margin: 5px 0 10px;
                padding: 10px 10px 20px 10px;
                -moz-border-radius: 5px;
                -khtml-border-radius: 5px;
                -webkit-border-radius: 5px;
                border-radius: 5px;
                border-radius: 0 0 0 15px;
                border: 3px solid #39f;
            }

            fieldset:hover {
                border-color: #bbb;
            }

            fieldset legend {
                color: #777;
                font-size: 14px;
                font-weight: 700;
                cursor: pointer;
                display: block;
                text-shadow: 1px 1px 1px #fff;
                min-width: 90px;
                padding: 0 3px 0 3px;
                border: 1px solid #95abff;
                text-align: center;
                line-height: 30px;
            }

            fieldset .line {
                border-bottom: 1px solid #e5e5e5;
                padding-bottom: 15px;
            }
        </style>


        <script type="text/javascript">
            jQuery(document).ready(function($) {


                $(".toggle").click(function() {
                    $(this).next().slideToggle('normal')
                });


            });
        </script>


        <form action="#" method="post" enctype="multipart/form-data" name="psn_api_form" id="psn_api_form">


            <div class="wrap">


                <div id="icon-options-general" class="icon32"><br></div>


                <h2>PSN 游戏列表设置</h2><br>


                <fieldset>


                    <legend class="toggle">插件配置</legend>


                    <div>


                        <table width="100%" border="1" class="otakutable">
                            <tr>
                                <td>
                                    <h3>接口配置</h3>
                                    <hr>
                                </td>
                                <td></td>
                            </tr>

                            <tr>
                                <td>API 插件安装站接口(留空即为本站):</td>
                                <td><label><input type="text" name="<?php echo $this->api_base ?>" rows="1" style="width:410px;" value="<?php echo ($options[$this->api_base]); ?>"></label></td>
                            </tr>

                            <tr>
                                <td>单次请求数量:</td>
                                <td><label><input type="number" name="<?php echo $this->api_limit ?>" rows="1" style="width:410px;" value="<?php echo ($options[$this->api_limit]); ?>"></label></td>
                            </tr>
                            <tr>
                                <td>卡片宽度:</td>
                                <td><label><input type="number" name="<?php echo $this->card_width ?>" rows="1" style="width:410px;" value="<?php echo ($options[$this->card_width]); ?>"></label></td>
                            </tr>
                            <tr>
                                <td>卡片间距:</td>
                                <td><label><input type="number" name="<?php echo $this->list_padding ?>" rows="1" style="width:410px;" value="<?php echo ($options[$this->list_padding]); ?>"></label></td>
                            </tr>
                            <tr>
                                <td>全局资源注册：</td>
                                <td><label><input name="<?php echo $this->ajax_fix ?>" type="checkbox" <?php if (boolval($options[$this->ajax_fix])) echo "checked='checked'"; ?> /> 开启</label></td>
                            </tr>
                            <tr>
                                <td>说明</td>
                                <td><label>全局资源注册将js与css注册至全局，适用于兼容 Ajax 的情况</td>
                            </tr>
                            <br><br>
                            <tr>
                                <td>自动开始：</td>
                                <td><label><input name="<?php echo $this->auto_runjs ?>" type="checkbox" <?php if (boolval($options[$this->auto_runjs])) echo "checked='checked'"; ?> /> 开启</label></td>
                            </tr>
                            <tr>
                                <td>说明</td>
                                <td><label>有的主题 Ajax 加载后不会执行其内的 js 方法，此时请取消勾选"自动开始"，然后在主题如下位置处执行初始化方法</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td><img src="<?php echo plugins_url('assets/image/fix-ajax-callback.jpg', __FILE__) ?>" alt="" srcset=""> </td>
                            </tr>
                        </table>
                    </div>


                </fieldset>

              

                <!-- 提交按钮 -->
                <p class="submit">
                    <input type="submit" name="psn-gamelist-save" value="更新信息" /></p>

                <fieldset>
                    <legend class="toggle">Bug反馈与联系作者</legend>
                    <div>
                        <table width="800" border="1" class="otakutable">
                            <tr>
                                <td>邮箱</td>
                                <td><label><a href="mailto:admin@azimiao.com" target="_blank">admin@azimiao.com</a></label></td>
                            </tr>
                            <tr>
                                <td>博客</td>
                                <td><label><a href="//www.azimiao.com" target="_blank">梓喵出没(www.azimiao.com)</a></label></td>
                            </tr>
                            <tr>
                                <td>交流</td>
                                <td><label><a href="//jq.qq.com/?_wv=1027&k=57B5rBh" target="_blank">梓喵出没博客交流群</a></label></td>
                            </tr>
                            <tr>
                                <td>声明</td>
                                <td>此插件允许修改自用，禁止二次分发。禁止将此插件内任意代码、样式集成至其他项目。</td>
                            </tr>
                            <tr>
                                <td>版本</td>
                                <td><a href="https://github.com/Azimiao/" target="_blank"><?php echo $this->plugin_version ?></a></td>
                            </tr>
                        </table>
                    </div>
                </fieldset>

            </div>
            <input type="hidden" id="<?php echo $this->nonceflagName ?>" name="<?php echo $this->nonceflagName ?>" value="<?php echo wp_create_nonce($this->nonceflag); ?>" />
        </form>

    <?php
    }
}
new Azimiao_PSN_List();
?>