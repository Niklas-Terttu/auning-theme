<?php
/** GitHub updates for the active Auning parent theme. */
if (!defined('ABSPATH')) { exit; }

class Auning_GitHub_Updater {
    const REPO = 'Niklas-Terttu/auning-theme';
    const CACHE = 'auning_github_update_v1';
    private $slug;

    public function __construct() {
        $this->slug = get_template();
        add_filter('pre_set_site_transient_update_themes', array($this, 'filter_updates'));
        add_filter('upgrader_source_selection', array($this, 'source_directory'), 10, 4);
        add_action('admin_menu', array($this, 'menu'));
        add_action('network_admin_menu', array($this, 'menu'));
        add_action('admin_post_auning_check_updates', array($this, 'manual_check'));
        add_action('upgrader_process_complete', array($this, 'clear_after_update'), 10, 2);
    }

    private function request($url) {
        $response = wp_remote_get($url, array(
            'timeout' => 15,
            'headers' => array('Accept' => 'application/vnd.github+json', 'User-Agent' => 'Auning-Theme-Updater'),
        ));
        if (is_wp_error($response)) { return new WP_Error('connection', 'Kunne ikke kontakte GitHub. Prøv igen senere.'); }
        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            return new WP_Error('github_http', $code === 403 || $code === 429
                ? 'GitHub har midlertidigt begrænset antallet af forespørgsler. Prøv igen senere.'
                : 'GitHub svarede med HTTP ' . (int) $code . '. Opdateringen kunne ikke kontrolleres.');
        }
        return wp_remote_retrieve_body($response);
    }

    public function latest($force = false) {
        $cached = get_site_transient(self::CACHE);
        if (!$force && is_array($cached)) { return $cached; }
        $body = $this->request('https://api.github.com/repos/' . self::REPO . '/commits/main');
        if (is_wp_error($body)) { return $this->remember_error($body); }
        $commit = json_decode($body, true);
        if (empty($commit['sha']) || !preg_match('/^[a-f0-9]{40}$/', $commit['sha'])) {
            return $this->remember_error(new WP_Error('invalid_commit', 'GitHub returnerede et ugyldigt versionssvar.'));
        }
        // Pin metadata and ZIP to the same immutable commit.
        $sha = $commit['sha'];
        $css = $this->request('https://raw.githubusercontent.com/' . self::REPO . '/' . $sha . '/style.css');
        if (is_wp_error($css)) { return $this->remember_error($css); }
        if (!preg_match('/^[ \t]*Version:\s*([0-9]+\.[0-9]+\.[0-9]+(?:[-+][a-zA-Z0-9.\-]+)?)[ \t]*$/m', $css, $version)) {
            return $this->remember_error(new WP_Error('invalid_version', 'Version kunne ikke læses fra temaets style.css.'));
        }
        $data = array('version' => $version[1], 'sha' => $sha, 'checked' => time());
        set_site_transient(self::CACHE, $data, 6 * HOUR_IN_SECONDS);
        return $data;
    }

    private function remember_error($error) {
        $data = array('error' => $error->get_error_message(), 'checked' => time());
        set_site_transient(self::CACHE, $data, 5 * MINUTE_IN_SECONDS);
        return $data;
    }

    public function filter_updates($transient) {
        if (!is_object($transient) || empty($transient->checked[$this->slug])) { return $transient; }
        $latest = $this->latest();
        // Preserve previously known updates during temporary connection failures.
        if (!empty($latest['error'])) { return $transient; }
        $item = array(
            'theme' => $this->slug,
            'new_version' => $latest['version'],
            'url' => 'https://github.com/' . self::REPO,
            'package' => 'https://codeload.github.com/' . self::REPO . '/zip/' . $latest['sha'],
            'requires' => '6.0',
            'requires_php' => '7.4',
        );
        if (version_compare($latest['version'], $transient->checked[$this->slug], '>')) {
            $transient->response[$this->slug] = $item;
            unset($transient->no_update[$this->slug]);
        } else {
            $item['package'] = '';
            $transient->no_update[$this->slug] = $item;
            unset($transient->response[$this->slug]);
        }
        return $transient;
    }

    public function source_directory($source, $remote_source, $upgrader, $extra = array()) {
        if (is_wp_error($source) || !is_array($extra) || ($extra['type'] ?? '') !== 'theme'
            || ($extra['action'] ?? '') !== 'update' || ($extra['theme'] ?? '') !== $this->slug) {
            return $source;
        }
        // GitHub ZIPs use repo-commit as their folder. Preserve the installed theme slug,
        // including installations originally uploaded with GitHub's Download ZIP.
        if (basename(untrailingslashit($source)) === $this->slug) { return $source; }
        global $wp_filesystem;
        $destination = trailingslashit($remote_source) . $this->slug;
        if (!$wp_filesystem || !$wp_filesystem->move($source, $destination)) {
            return new WP_Error('auning_directory', 'Temaets opdateringsmappe kunne ikke klargøres. Det installerede tema er ikke ændret.');
        }
        return trailingslashit($destination);
    }

    public function menu() {
        add_submenu_page('themes.php', 'Auning tema – opdateringer', 'Auning opdateringer',
            'update_themes', 'auning-updates', array($this, 'page'));
    }

    private function page_url() {
        return is_multisite() ? network_admin_url('themes.php?page=auning-updates') : admin_url('themes.php?page=auning-updates');
    }

    public function manual_check() {
        if (!current_user_can('update_themes')) { wp_die('Du har ikke adgang til at opdatere temaer.'); }
        check_admin_referer('auning_check_updates');
        $this->latest(true);
        // Clear the core throttle, then run the ordinary WordPress update check.
        delete_site_transient('update_themes');
        wp_update_themes();
        wp_safe_redirect($this->page_url());
        exit;
    }

    public function page() {
        if (!current_user_can('update_themes')) { return; }
        $theme = wp_get_theme($this->slug);
        $data = get_site_transient(self::CACHE);
        echo '<div class="wrap"><h1>Auning tema – opdateringer</h1>';
        echo '<p>Installeret version: <strong>' . esc_html($theme->get('Version')) . '</strong></p>';
        if (is_array($data)) {
            if (!empty($data['error'])) {
                echo '<div class="notice notice-error inline"><p>' . esc_html($data['error']) . '</p></div>';
            } elseif (!empty($data['version'])) {
                echo '<p>Seneste version på GitHub: <strong>' . esc_html($data['version']) . '</strong></p>';
                if (version_compare($data['version'], $theme->get('Version'), '>')) {
                    echo '<div class="notice notice-info inline"><p>En ny version er tilgængelig.</p></div>';
                    $updates = get_site_transient('update_themes');
                    if (!empty($updates->response[$this->slug])) {
                        $url = wp_nonce_url(self_admin_url('update.php?action=upgrade-theme&theme=' . rawurlencode($this->slug)), 'upgrade-theme_' . $this->slug);
                        echo '<p><a class="button button-primary" href="' . esc_url($url) . '">Installér opdatering</a></p>';
                    } else {
                        echo '<p>Tryk på Søg efter opdatering for at klargøre installationen.</p>';
                    }
                } else {
                    echo '<div class="notice notice-success inline"><p>Du har den nyeste version.</p></div>';
                }
            }
            echo '<p>Senest kontrolleret: ' . esc_html(wp_date('d-m-Y H:i', $data['checked'])) . '</p>';
        }
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<input type="hidden" name="action" value="auning_check_updates">';
        wp_nonce_field('auning_check_updates');
        submit_button('Søg efter opdatering', 'secondary');
        echo '</form><p>Temaet kontrollerer også GitHub ved WordPress’ almindelige opdateringstjek. Nye versioner kræver et højere versionsnummer i style.css. Automatiske installationer kan aktiveres via WordPress’ temaoversigt.</p></div>';
    }

    public function clear_after_update($upgrader, $extra) {
        if (($extra['type'] ?? '') !== 'theme' || ($extra['action'] ?? '') !== 'update') { return; }
        $themes = isset($extra['themes']) ? (array) $extra['themes'] : array($extra['theme'] ?? '');
        if (in_array($this->slug, $themes, true)) { delete_site_transient(self::CACHE); }
    }
}
new Auning_GitHub_Updater();
