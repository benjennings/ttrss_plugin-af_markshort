<?php

//
// More info about this plugin in: http://protagonist.co.uk/markshort
// Preferences pane from: https://github.com/mbirth/ttrss_plugin-af_feedmod
// Other inspiration from: https://github.com/atallo/ttrss_fullpost

class af_MarkShort extends Plugin implements IHandler{
    private $host;

    function about() {
        return array(0.01,
            "MarkShort. Appends a [short] marker to the end of a title when a short article is detected.",
            "benjennings");
    }

    function api_version() {
        return 2;
    }

    function init($host) {
        $this->host = $host;

        $host->add_hook($host::HOOK_PREFS_TABS, $this);
        $host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
    }

    function hook_article_filter($article) {
        $json_conf = $this->host->get($this, 'json_conf');
        $owner_uid = $article['owner_uid'];
        $data = json_decode($json_conf, true);

        if (!is_array($data)) {
            // no valid JSON or no configuration at all
            return $article;
        }

        foreach ($data as $urlpart) {
            if (stripos($article['link'], $urlpart) === false) continue; // skip this entry, if the URL doesn't match
            if (strpos($article['plugin_data'], "markshort,$owner_uid:") !== false) {
                // do not process an article more than once
                if (isset($article['stored']['content'])) $article['content'] = $article['stored']['content'];
                break;
            }
            try {
                $article['title'] = $article['title'].' '.$this->get_article_length($article['content']);
                $article['plugin_data'] = "markshort,$owner_uid:" . $article['plugin_data'];
            } catch (Exception $e) {
                // just in case
            }
            break;
        }

        return $article;
    }

    private function get_article_length($content) {
        include_once 'html2text.php';
        $threshhold = 500; // this is the minimum article content size, change if you like

        $content_length = convert_html_to_text($content);
        if (str_word_count($content_length, 0) < $threshhold) {
            return '[short]';
        } else {
            return ''; // set to nothing at the moment
        }
    }


//
// This section deals with the UI for saving of feed preferences
//
function hook_prefs_tabs($args)
{
    print '<div id="markshortConfigTab" dojoType="dijit.layout.ContentPane"
                href="backend.php?op=af_markshort"
                title="' . __('MarkShort') . '"></div>';
}

function index()
{
    $pluginhost = PluginHost::getInstance();
    $json_conf = $pluginhost->get($this, 'json_conf');

    print "<form dojoType=\"dijit.form.Form\">";

    print "<script type=\"dojo/method\" event=\"onSubmit\" args=\"evt\">
        evt.preventDefault();
        if (this.validate()) {
            new Ajax.Request('backend.php', {
                parameters: dojo.objectToQuery(this.getValues()),
                onComplete: function(transport) {
                    if (transport.responseText.indexOf('error')>=0) notify_error(transport.responseText);
                    else notify_info(transport.responseText);
                }
            });
            //this.reset();
        }
        </script>";

    print "<input dojoType=\"dijit.form.TextBox\" style=\"display : none\" name=\"op\" value=\"pluginhandler\">";
    print "<input dojoType=\"dijit.form.TextBox\" style=\"display : none\" name=\"method\" value=\"save\">";
    print "<input dojoType=\"dijit.form.TextBox\" style=\"display : none\" name=\"plugin\" value=\"af_markshort\">";

    print "<table width='100%'><tr><td>";
    print "<textarea dojoType=\"dijit.form.SimpleTextarea\" name=\"json_conf\" style=\"font-size: 12px; width: 99%; height: 500px;\">$json_conf</textarea>";
    print "</td></tr></table>";

    print "<p><button dojoType=\"dijit.form.Button\" type=\"submit\">".__("Save")."</button>";

    print "</form>";
}

function save()
{
    $json_conf = $_POST['json_conf'];

    if (is_null(json_decode($json_conf))) {
        echo __("error: Invalid JSON!");
        return false;
    }

    $this->host->set($this, 'json_conf', $json_conf);
    echo __("Configuration saved.");
}

function csrf_ignore($method)
{
    $csrf_ignored = array("index", "edit");
    return array_search($method, $csrf_ignored) !== false;
}

function before($method)
{
    if ($_SESSION["uid"]) {
        return true;
    }
    return false;
}

function after()
{
    return true;
}
//


}
?>
