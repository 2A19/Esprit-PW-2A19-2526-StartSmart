<?php
class TranslationService {
    /**
     * Translates text using a reliable, cost-free public endpoint.
     * Perfect for school projects without needing an API key.
     */
    public static function translate($text, $targetLang) {
        if ($targetLang === 'original') {
            return $text;
        }

        // Decode entities and strip HTML so the API doesn't get confused
        $cleanText = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $cleanText = strip_tags($cleanText);

        if (empty(trim($cleanText))) {
            return $text;
        }

        // Google Translate free public endpoint
        $url = 'https://translate.googleapis.com/translate_a/single?client=gtx&sl=auto&tl=' . $targetLang . '&dt=t&q=' . urlencode($cleanText);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Prevent local XAMPP SSL errors
        $response = curl_exec($ch);
        curl_close($ch);

        $translatedText = $cleanText;
        if ($response) {
            $json = json_decode($response, true);
            if (isset($json[0]) && is_array($json[0])) {
                $translatedText = '';
                foreach ($json[0] as $segment) {
                    $translatedText .= $segment[0];
                }
            }
        }
        
        $langNames = ['en' => 'Anglais', 'ar' => 'Arabe', 'fr' => 'Français'];
        $langName = $langNames[$targetLang] ?? strtoupper($targetLang);
        
        // Decode any entities returned by Google Translate before escaping them again to prevent double encoding
        $decodedTranslated = html_entity_decode($translatedText, ENT_QUOTES, 'UTF-8');
        $translatedHtml = nl2br(htmlspecialchars($decodedTranslated, ENT_QUOTES, 'UTF-8'));
        
        return "<div style='display: inline-flex; align-items: center; gap: 6px; background: #f0f4f8; color: #576574; padding: 4px 8px; border-radius: 6px; font-size: 0.75em; font-weight: 600; margin-bottom: 10px;'><i class=\"fa-solid fa-language\" style=\"color: #3498db;\"></i> Traduit en " . $langName . "</div><br>" . $translatedHtml;
    }
}
?>
