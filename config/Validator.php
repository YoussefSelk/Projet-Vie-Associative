<?php
/**
 * =============================================================================
 * CLASSE DE VALIDATION ET ASSAINISSEMENT DES DONNÉES
 * =============================================================================
 * 
 * Fournit un ensemble complet de méthodes pour :
 * - Assainir (sanitize) les entrées utilisateur
 * - Valider les formats de données (email, téléphone, URL, etc.)
 * - Vérifier les contraintes (longueur, plage, format)
 * - Collecter et gérer les erreurs de validation
 * 
 * Utilisation recommandée :
 * ```php
 * $validator = new Validator();
 * 
 * // Assainissement
 * $nom = $validator->sanitizeString($_POST['nom']);
 * $email = $validator->sanitizeEmail($_POST['email']);
 * 
 * // Validation avec collecte d'erreurs
 * $validator->validateRequired($nom, 'nom', 'Le nom est requis');
 * $validator->validateEmail($email, 'email');
 * $validator->validateLength($nom, 2, 50, 'nom');
 * 
 * if ($validator->hasErrors()) {
 *     $errors = $validator->getErrors();
 * }
 * ```
 * 
 * Sécurité :
 * - Protection contre les injections XSS
 * - Protection contre les injections SQL (via PDO prepared statements)
 * - Validation stricte des formats
 * - Expressions régulières sécurisées
 * 
 * @author Équipe de développement EILCO
 * @version 1.0
 * @since 2026
 */

class Validator
{
    // =========================================================================
    // PROPRIÉTÉS
    // =========================================================================
    
    /** @var array Tableau des erreurs de validation collectées */
    private array $errors = [];
    
    /** @var array Données validées et assainies */
    private array $validated = [];
    
    /** @var array Messages d'erreur par défaut en français */
    private array $defaultMessages = [
        'required' => 'Le champ :field est obligatoire.',
        'email' => 'L\'adresse email n\'est pas valide.',
        'url' => 'L\'URL n\'est pas valide.',
        'phone' => 'Le numéro de téléphone n\'est pas valide.',
        'min_length' => 'Le champ :field doit contenir au moins :min caractères.',
        'max_length' => 'Le champ :field ne doit pas dépasser :max caractères.',
        'length_between' => 'Le champ :field doit contenir entre :min et :max caractères.',
        'numeric' => 'Le champ :field doit être un nombre.',
        'integer' => 'Le champ :field doit être un nombre entier.',
        'min_value' => 'Le champ :field doit être supérieur ou égal à :min.',
        'max_value' => 'Le champ :field doit être inférieur ou égal à :max.',
        'alpha' => 'Le champ :field ne doit contenir que des lettres.',
        'alphanumeric' => 'Le champ :field ne doit contenir que des lettres et des chiffres.',
        'date' => 'La date n\'est pas valide.',
        'date_format' => 'La date doit être au format :format.',
        'date_before' => 'La date doit être antérieure à :date.',
        'date_after' => 'La date doit être postérieure à :date.',
        'in_list' => 'La valeur sélectionnée n\'est pas valide.',
        'not_in_list' => 'La valeur sélectionnée n\'est pas autorisée.',
        'regex' => 'Le format du champ :field n\'est pas valide.',
        'confirmed' => 'La confirmation du champ :field ne correspond pas.',
        'unique' => 'Cette valeur existe déjà.',
        'file_required' => 'Le fichier est obligatoire.',
        'file_type' => 'Le type de fichier n\'est pas autorisé.',
        'file_size' => 'Le fichier ne doit pas dépasser :max Mo.',
        'password_strength' => 'Le mot de passe doit contenir au moins :requirements.',
        'french_phone' => 'Le numéro de téléphone français n\'est pas valide.',
        'postal_code' => 'Le code postal n\'est pas valide.',
        'iban' => 'L\'IBAN n\'est pas valide.',
        'siret' => 'Le numéro SIRET n\'est pas valide.',
    ];
    
    // =========================================================================
    // CONSTRUCTEUR
    // =========================================================================
    
    /**
     * Constructeur de la classe Validator
     * Initialise les tableaux d'erreurs et de données validées
     */
    public function __construct()
    {
        $this->reset();
    }
    
    /**
     * Réinitialise le validateur
     * Efface toutes les erreurs et données validées
     * 
     * @return self Instance pour chaînage
     */
    public function reset(): self
    {
        $this->errors = [];
        $this->validated = [];
        return $this;
    }
    
    // =========================================================================
    // MÉTHODES D'ASSAINISSEMENT (SANITIZATION)
    // =========================================================================
    
    /**
     * Assainit une chaîne de caractères
     * - Supprime les espaces en début/fin
     * - Encode les caractères HTML spéciaux
     * - Protège contre les attaques XSS
     * 
     * @param mixed $value Valeur à assainir
     * @param bool $preserveNewlines Conserver les retours à la ligne
     * @return string Chaîne assainie
     */
    public function sanitizeString(mixed $value, bool $preserveNewlines = false): string
    {
        if ($value === null || $value === false) {
            return '';
        }
        
        $value = trim((string) $value);
        
        if ($preserveNewlines) {
            // Convertit les retours à la ligne en placeholder, assainit, puis restaure
            $value = str_replace(["\r\n", "\r", "\n"], "{{NEWLINE}}", $value);
            $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $value = str_replace("{{NEWLINE}}", "\n", $value);
        } else {
            $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        
        return $value;
    }
    
    /**
     * Assainit un tableau de chaînes récursivement
     * 
     * @param array $array Tableau à assainir
     * @return array Tableau assaini
     */
    public function sanitizeArray(array $array): array
    {
        return array_map(function ($item) {
            if (is_array($item)) {
                return $this->sanitizeArray($item);
            }
            return $this->sanitizeString($item);
        }, $array);
    }
    
    /**
     * Assainit une adresse email
     * - Supprime les caractères non autorisés
     * - Convertit en minuscules
     * 
     * @param string $email Email à assainir
     * @return string Email assaini
     */
    public function sanitizeEmail(string $email): string
    {
        $email = trim($email);
        $email = strtolower($email);
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }
    
    /**
     * Assainit une URL
     * - Encode les caractères spéciaux
     * - Supprime les caractères non autorisés
     * 
     * @param string $url URL à assainir
     * @return string URL assainie
     */
    public function sanitizeUrl(string $url): string
    {
        $url = trim($url);
        return filter_var($url, FILTER_SANITIZE_URL);
    }
    
    /**
     * Assainit un nombre entier
     * 
     * @param mixed $value Valeur à assainir
     * @param int $default Valeur par défaut si non valide
     * @return int Entier assaini
     */
    public function sanitizeInt(mixed $value, int $default = 0): int
    {
        $filtered = filter_var($value, FILTER_VALIDATE_INT);
        return $filtered !== false ? $filtered : $default;
    }
    
    /**
     * Assainit un nombre décimal
     * 
     * @param mixed $value Valeur à assainir
     * @param float $default Valeur par défaut si non valide
     * @return float Décimal assaini
     */
    public function sanitizeFloat(mixed $value, float $default = 0.0): float
    {
        // Remplace la virgule par un point pour les formats français
        if (is_string($value)) {
            $value = str_replace(',', '.', $value);
        }
        
        $filtered = filter_var($value, FILTER_VALIDATE_FLOAT);
        return $filtered !== false ? $filtered : $default;
    }
    
    /**
     * Assainit un numéro de téléphone
     * - Supprime tous les caractères non numériques sauf +
     * - Formate le numéro de manière standard
     * 
     * @param string $phone Numéro à assainir
     * @return string Numéro assaini
     */
    public function sanitizePhone(string $phone): string
    {
        // Supprime tout sauf les chiffres et le +
        $phone = preg_replace('/[^\d+]/', '', $phone);
        
        // Convertit 0033 en +33
        if (str_starts_with($phone, '0033')) {
            $phone = '+33' . substr($phone, 4);
        }
        
        return $phone;
    }
    
    /**
     * Assainit un nom de fichier
     * - Supprime les caractères dangereux
     * - Remplace les espaces par des underscores
     * - Limite la longueur
     * 
     * @param string $filename Nom de fichier à assainir
     * @param int $maxLength Longueur maximale
     * @return string Nom de fichier assaini
     */
    public function sanitizeFilename(string $filename, int $maxLength = 255): string
    {
        // Supprime les caractères dangereux
        $filename = preg_replace('/[^\w\s\-\.]/', '', $filename);
        
        // Remplace les espaces par des underscores
        $filename = preg_replace('/\s+/', '_', $filename);
        
        // Supprime les points multiples
        $filename = preg_replace('/\.+/', '.', $filename);
        
        // Limite la longueur
        if (strlen($filename) > $maxLength) {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $name = substr($name, 0, $maxLength - strlen($ext) - 1);
            $filename = $name . '.' . $ext;
        }
        
        return $filename;
    }
    
    /**
     * Assainit un slug (URL-friendly)
     * - Convertit en minuscules
     * - Remplace les caractères accentués
     * - Supprime les caractères spéciaux
     * 
     * @param string $text Texte à convertir en slug
     * @return string Slug assaini
     */
    public function sanitizeSlug(string $text): string
    {
        // Caractères accentués à remplacer
        $accents = [
            'à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a', 'ã' => 'a',
            'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'é' => 'e',
            'ì' => 'i', 'î' => 'i', 'ï' => 'i', 'í' => 'i',
            'ò' => 'o', 'ô' => 'o', 'ö' => 'o', 'ó' => 'o', 'õ' => 'o',
            'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ú' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
            'ñ' => 'n', 'ç' => 'c',
            'œ' => 'oe', 'æ' => 'ae',
        ];
        
        $text = strtolower(trim($text));
        $text = strtr($text, $accents);
        $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        $text = trim($text, '-');
        
        return $text;
    }
    
    /**
     * Supprime les balises HTML d'une chaîne
     * Optionnellement autorise certaines balises
     * 
     * @param string $html HTML à nettoyer
     * @param array $allowedTags Balises autorisées ['p', 'br', 'strong']
     * @return string Texte nettoyé
     */
    public function stripHtml(string $html, array $allowedTags = []): string
    {
        if (empty($allowedTags)) {
            return strip_tags($html);
        }
        
        $allowed = '<' . implode('><', $allowedTags) . '>';
        return strip_tags($html, $allowed);
    }
    
    // =========================================================================
    // MÉTHODES DE VALIDATION
    // =========================================================================
    
    /**
     * Valide qu'un champ est requis (non vide)
     * 
     * @param mixed $value Valeur à valider
     * @param string $field Nom du champ (pour le message d'erreur)
     * @param string|null $message Message d'erreur personnalisé
     * @return bool True si valide
     */
    public function validateRequired(mixed $value, string $field, ?string $message = null): bool
    {
        $isValid = !($value === null || $value === '' || (is_array($value) && empty($value)));
        
        if (!$isValid) {
            $this->addError($field, $message ?? $this->formatMessage('required', ['field' => $field]));
        }
        
        return $isValid;
    }
    
    /**
     * Valide une adresse email
     * 
     * @param string $email Email à valider
     * @param string $field Nom du champ
     * @param string|null $message Message d'erreur personnalisé
     * @return bool True si valide
     */
    public function validateEmail(string $email, string $field = 'email', ?string $message = null): bool
    {
        $isValid = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        
        if (!$isValid) {
            $this->addError($field, $message ?? $this->defaultMessages['email']);
        }
        
        return $isValid;
    }
    
    /**
     * Valide une adresse email avec domaine spécifique
     * Utile pour restreindre aux emails professionnels/universitaires
     * 
     * @param string $email Email à valider
     * @param array $allowedDomains Domaines autorisés ['eilco-ulco.fr', 'univ-littoral.fr']
     * @param string $field Nom du champ
     * @param string|null $message Message d'erreur personnalisé
     * @return bool True si valide
     */
    public function validateEmailDomain(string $email, array $allowedDomains, string $field = 'email', ?string $message = null): bool
    {
        if (!$this->validateEmail($email, $field, $message)) {
            return false;
        }
        
        $domain = strtolower(substr(strrchr($email, '@'), 1));
        $isValid = in_array($domain, array_map('strtolower', $allowedDomains));
        
        if (!$isValid) {
            $this->addError($field, $message ?? "L'email doit appartenir à l'un des domaines : " . implode(', ', $allowedDomains));
        }
        
        return $isValid;
    }
    
    /**
     * Valide une URL
     * 
     * @param string $url URL à valider
     * @param string $field Nom du champ
     * @param bool $requireHttps Exiger HTTPS
     * @param string|null $message Message d'erreur personnalisé
     * @return bool True si valide
     */
    public function validateUrl(string $url, string $field = 'url', bool $requireHttps = false, ?string $message = null): bool
    {
        $isValid = filter_var($url, FILTER_VALIDATE_URL) !== false;
        
        if ($isValid && $requireHttps) {
            $isValid = str_starts_with(strtolower($url), 'https://');
        }
        
        if (!$isValid) {
            $this->addError($field, $message ?? $this->defaultMessages['url']);
        }
        
        return $isValid;
    }
    
    /**
     * Valide un numéro de téléphone (format international)
     * 
     * @param string $phone Numéro à valider
     * @param string $field Nom du champ
     * @param string|null $message Message d'erreur personnalisé
     * @return bool True si valide
     */
    public function validatePhone(string $phone, string $field = 'phone', ?string $message = null): bool
    {
        // Format international : +XX XXXXXXXXXX ou format local
        $pattern = '/^(\+?\d{1,4})?[\s\-\.]?\(?\d{1,4}\)?[\s\-\.]?\d{1,4}[\s\-\.]?\d{1,9}$/';
        $isValid = preg_match($pattern, $phone) === 1;
        
        if (!$isValid) {
            $this->addError($field, $message ?? $this->defaultMessages['phone']);
        }
        
        return $isValid;
    }
    
    /**
     * Valide un numéro de téléphone français
     * Formats acceptés : 0612345678, 06 12 34 56 78, +33612345678
     * 
     * @param string $phone Numéro à valider
     * @param string $field Nom du champ
     * @param string|null $message Message d'erreur personnalisé
     * @return bool True si valide
     */
    public function validateFrenchPhone(string $phone, string $field = 'phone', ?string $message = null): bool
    {
        // Nettoie le numéro
        $cleanPhone = preg_replace('/[\s\-\.]/', '', $phone);
        
        // Formats français : 0X XX XX XX XX ou +33X XX XX XX XX
        $pattern = '/^(?:(?:\+|00)33|0)\s*[1-9](?:[\s\.\-]*\d{2}){4}$/';
        $isValid = preg_match($pattern, $cleanPhone) === 1;
        
        if (!$isValid) {
            $this->addError($field, $message ?? $this->defaultMessages['french_phone']);
        }
        
        return $isValid;
    }
    
    /**
     * Valide la longueur d'une chaîne
     * 
     * @param string $value Valeur à valider
     * @param int $min Longueur minimale
     * @param int $max Longueur maximale
     * @param string $field Nom du champ
     * @param string|null $message Message d'erreur personnalisé
     * @return bool True si valide
     */
    public function validateLength(string $value, int $min, int $max, string $field, ?string $message = null): bool
    {
        $length = mb_strlen($value, 'UTF-8');
        $isValid = $length >= $min && $length <= $max;
        
        if (!$isValid) {
            if ($length < $min) {
                $this->addError($field, $message ?? $this->formatMessage('min_length', ['field' => $field, 'min' => $min]));
            } else {
                $this->addError($field, $message ?? $this->formatMessage('max_length', ['field' => $field, 'max' => $max]));
            }
        }
        
        return $isValid;
    }
    
    /**
     * Valide la longueur minimale d'une chaîne
     * 
     * @param string $value Valeur à valider
     * @param int $min Longueur minimale
     * @param string $field Nom du champ
     * @param string|null $message Message d'erreur personnalisé
     * @return bool True si valide
     */
    public function validateMinLength(string $value, int $min, string $field, ?string $message = null): bool
    {
        $isValid = mb_strlen($value, 'UTF-8') >= $min;
        
        if (!$isValid) {
            $this->addError($field, $message ?? $this->formatMessage('min_length', ['field' => $field, 'min' => $min]));
        }
        
        return $isValid;
    }
    
    /**
     * Valide la longueur maximale d'une chaîne
     * 
     * @param string $value Valeur à valider
     * @param int $max Longueur maximale
     * @param string $field Nom du champ
     * @param string|null $message Message d'erreur personnalisé
     * @return bool True si valide
     */
    public function validateMaxLength(string $value, int $max, string $field, ?string $message = null): bool
    {
        $isValid = mb_strlen($value, 'UTF-8') <= $max;
        
        if (!$isValid) {
            $this->addError($field, $message ?? $this->formatMessage('max_length', ['field' => $field, 'max' => $max]));
        }
        
        return $isValid;
    }
    
    /**
     * Valide qu'une valeur est numérique
     * 
     * @param mixed $value Valeur à valider
     * @param string $field Nom du champ
     * @param string|null $message Message d'erreur personnalisé
     * @return bool True si valide
     */
    public function validateNumeric(mixed $value, string $field, ?string $message = null): bool
    {
        $isValid = is_numeric($value);
        
        if (!$isValid) {
            $this->addError($field, $message ?? $this->formatMessage('numeric', ['field' => $field]));
        }
        
        return $isValid;
    }
    
    /**
     * Valide qu'une valeur est un entier
     * 
     * @param mixed $value Valeur à valider
     * @param string $field Nom du champ
     * @param string|null $message Message d'erreur personnalisé
     * @return bool True si valide
     */
    public function validateInteger(mixed $value, string $field, ?string $message = null): bool
    {
        $isValid = filter_var($value, FILTER_VALIDATE_INT) !== false;
        
        if (!$isValid) {
            $this->addError($field, $message ?? $this->formatMessage('integer', ['field' => $field]));
        }
        
        return $isValid;
    }
    
    /**
     * Valide une plage de valeurs numériques
     * 
     * @param mixed $value Valeur à valider
     * @param float|null $min Valeur minimale (null = pas de limite)
     * @param float|null $max Valeur maximale (null = pas de limite)
     * @param string $field Nom du champ
     * @param string|null $message Message d'erreur personnalisé
     * @return bool True si valide
     */
    public function validateRange(mixed $value, ?float $min, ?float $max, string $field, ?string $message = null): bool
    {
        if (!is_numeric($value)) {
            $this->addError($field, $this->formatMessage('numeric', ['field' => $field]));
            return false;
        }
        
        $numValue = (float) $value;
        $isValid = true;
        
        if ($min !== null && $numValue < $min) {
            $isValid = false;
            $this->addError($field, $message ?? $this->formatMessage('min_value', ['field' => $field, 'min' => $min]));
        }
        
        if ($max !== null && $numValue > $max) {
            $isValid = false;
            $this->addError($field, $message ?? $this->formatMessage('max_value', ['field' => $field, 'max' => $max]));
        }
        
        return $isValid;
    }
    
    /**
     * Valide qu'une chaîne ne contient que des lettres
     * 
     * @param string $value Valeur à valider
     * @param string $field Nom du champ
     * @param bool $allowSpaces Autoriser les espaces
     * @param bool $allowAccents Autoriser les accents
     * @param string|null $message Message d'erreur personnalisé
     * @return bool True si valide
     */
    public function validateAlpha(string $value, string $field, bool $allowSpaces = true, bool $allowAccents = true, ?string $message = null): bool
    {
        $pattern = $allowAccents ? 'a-zA-ZÀ-ÿ' : 'a-zA-Z';
        if ($allowSpaces) {
            $pattern .= '\s';
        }
        
        $isValid = preg_match('/^[' . $pattern . ']+$/', $value) === 1;
        
        if (!$isValid) {
            $this->addError($field, $message ?? $this->formatMessage('alpha', ['field' => $field]));
        }
        
        return $isValid;
    }
    
    /**
     * Valide qu'une chaîne ne contient que des lettres et chiffres
     * 
     * @param string $value Valeur à valider
     * @param string $field Nom du champ
     * @param bool $allowSpaces Autoriser les espaces
     * @param string|null $message Message d'erreur personnalisé
     * @return bool True si valide
     */
    public function validateAlphanumeric(string $value, string $field, bool $allowSpaces = false, ?string $message = null): bool
    {
        $pattern = $allowSpaces ? '/^[a-zA-Z0-9\s]+$/' : '/^[a-zA-Z0-9]+$/';
        $isValid = preg_match($pattern, $value) === 1;
        
        if (!$isValid) {
            $this->addError($field, $message ?? $this->formatMessage('alphanumeric', ['field' => $field]));
        }
        
        return $isValid;
    }
    
    /**
     * Valide une date
     * 
     * @param string $date Date à valider
     * @param string $format Format attendu (Y-m-d par défaut)
     * @param string $field Nom du champ
     * @param string|null $message Message d'erreur personnalisé
     * @return bool True si valide
     */
    public function validateDate(string $date, string $format = 'Y-m-d', string $field = 'date', ?string $message = null): bool
    {
        $dateTime = \DateTime::createFromFormat($format, $date);
        $isValid = $dateTime && $dateTime->format($format) === $date;
        
        if (!$isValid) {
            $this->addError($field, $message ?? $this->formatMessage('date_format', ['format' => $format]));
        }
        
        return $isValid;
    }
    
    /**
     * Valide qu'une date est antérieure à une autre
     * 
     * @param string $date Date à valider
     * @param string $beforeDate Date limite
     * @param string $format Format des dates
     * @param string $field Nom du champ
     * @param string|null $message Message d'erreur personnalisé
     * @return bool True si valide
     */
    public function validateDateBefore(string $date, string $beforeDate, string $format = 'Y-m-d', string $field = 'date', ?string $message = null): bool
    {
        $dateTime = \DateTime::createFromFormat($format, $date);
        $beforeDateTime = \DateTime::createFromFormat($format, $beforeDate);
        
        if (!$dateTime || !$beforeDateTime) {
            $this->addError($field, $this->defaultMessages['date']);
            return false;
        }
        
        $isValid = $dateTime < $beforeDateTime;
        
        if (!$isValid) {
            $this->addError($field, $message ?? $this->formatMessage('date_before', ['date' => $beforeDate]));
        }
        
        return $isValid;
    }
    
    /**
     * Valide qu'une date est postérieure à une autre
     * 
     * @param string $date Date à valider
     * @param string $afterDate Date limite
     * @param string $format Format des dates
     * @param string $field Nom du champ
     * @param string|null $message Message d'erreur personnalisé
     * @return bool True si valide
     */
    public function validateDateAfter(string $date, string $afterDate, string $format = 'Y-m-d', string $field = 'date', ?string $message = null): bool
    {
        $dateTime = \DateTime::createFromFormat($format, $date);
        $afterDateTime = \DateTime::createFromFormat($format, $afterDate);
        
        if (!$dateTime || !$afterDateTime) {
            $this->addError($field, $this->defaultMessages['date']);
            return false;
        }
        
        $isValid = $dateTime > $afterDateTime;
        
        if (!$isValid) {
            $this->addError($field, $message ?? $this->formatMessage('date_after', ['date' => $afterDate]));
        }
        
        return $isValid;
    }
    
    /**
     * Valide qu'une valeur est dans une liste autorisée
     * 
     * @param mixed $value Valeur à valider
     * @param array $allowedValues Liste des valeurs autorisées
     * @param string $field Nom du champ
     * @param bool $strict Comparaison stricte (===)
     * @param string|null $message Message d'erreur personnalisé
     * @return bool True si valide
     */
    public function validateInList(mixed $value, array $allowedValues, string $field, bool $strict = true, ?string $message = null): bool
    {
        $isValid = in_array($value, $allowedValues, $strict);
        
        if (!$isValid) {
            $this->addError($field, $message ?? $this->defaultMessages['in_list']);
        }
        
        return $isValid;
    }
    
    /**
     * Valide qu'une valeur n'est PAS dans une liste interdite
     * 
     * @param mixed $value Valeur à valider
     * @param array $forbiddenValues Liste des valeurs interdites
     * @param string $field Nom du champ
     * @param string|null $message Message d'erreur personnalisé
     * @return bool True si valide
     */
    public function validateNotInList(mixed $value, array $forbiddenValues, string $field, ?string $message = null): bool
    {
        $isValid = !in_array($value, $forbiddenValues, true);
        
        if (!$isValid) {
            $this->addError($field, $message ?? $this->defaultMessages['not_in_list']);
        }
        
        return $isValid;
    }
    
    /**
     * Valide une valeur avec une expression régulière personnalisée
     * 
     * @param string $value Valeur à valider
     * @param string $pattern Expression régulière (avec délimiteurs)
     * @param string $field Nom du champ
     * @param string|null $message Message d'erreur personnalisé
     * @return bool True si valide
     */
    public function validateRegex(string $value, string $pattern, string $field, ?string $message = null): bool
    {
        $isValid = preg_match($pattern, $value) === 1;
        
        if (!$isValid) {
            $this->addError($field, $message ?? $this->formatMessage('regex', ['field' => $field]));
        }
        
        return $isValid;
    }
    
    /**
     * Valide la confirmation d'un champ (ex: mot de passe)
     * 
     * @param mixed $value Valeur du champ principal
     * @param mixed $confirmValue Valeur du champ de confirmation
     * @param string $field Nom du champ
     * @param string|null $message Message d'erreur personnalisé
     * @return bool True si les valeurs correspondent
     */
    public function validateConfirmed(mixed $value, mixed $confirmValue, string $field, ?string $message = null): bool
    {
        $isValid = $value === $confirmValue;
        
        if (!$isValid) {
            $this->addError($field, $message ?? $this->formatMessage('confirmed', ['field' => $field]));
        }
        
        return $isValid;
    }
    
    /**
     * Valide la force d'un mot de passe
     * 
     * @param string $password Mot de passe à valider
     * @param array $requirements Exigences ['min_length', 'uppercase', 'lowercase', 'number', 'special']
     * @param string $field Nom du champ
     * @param string|null $message Message d'erreur personnalisé
     * @return bool True si valide
     */
    public function validatePasswordStrength(string $password, array $requirements = [], string $field = 'password', ?string $message = null): bool
    {
        // Exigences par défaut
        $defaults = [
            'min_length' => 8,
            'uppercase' => true,
            'lowercase' => true,
            'number' => true,
            'special' => false,
        ];
        
        $requirements = array_merge($defaults, $requirements);
        $errors = [];
        
        if (strlen($password) < $requirements['min_length']) {
            $errors[] = $requirements['min_length'] . ' caractères minimum';
        }
        
        if ($requirements['uppercase'] && !preg_match('/[A-Z]/', $password)) {
            $errors[] = 'une majuscule';
        }
        
        if ($requirements['lowercase'] && !preg_match('/[a-z]/', $password)) {
            $errors[] = 'une minuscule';
        }
        
        if ($requirements['number'] && !preg_match('/[0-9]/', $password)) {
            $errors[] = 'un chiffre';
        }
        
        if ($requirements['special'] && !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors[] = 'un caractère spécial';
        }
        
        $isValid = empty($errors);
        
        if (!$isValid) {
            $this->addError($field, $message ?? 'Le mot de passe doit contenir : ' . implode(', ', $errors));
        }
        
        return $isValid;
    }
    
    /**
     * Valide un code postal français
     * 
     * @param string $postalCode Code postal à valider
     * @param string $field Nom du champ
     * @param string|null $message Message d'erreur personnalisé
     * @return bool True si valide
     */
    public function validatePostalCode(string $postalCode, string $field = 'postal_code', ?string $message = null): bool
    {
        // Code postal français : 5 chiffres, commence par 0-9 (sauf 00)
        $isValid = preg_match('/^(?:0[1-9]|[1-8]\d|9[0-8])\d{3}$/', $postalCode) === 1;
        
        if (!$isValid) {
            $this->addError($field, $message ?? $this->defaultMessages['postal_code']);
        }
        
        return $isValid;
    }
    
    /**
     * Valide un numéro SIRET français
     * 
     * @param string $siret SIRET à valider
     * @param string $field Nom du champ
     * @param string|null $message Message d'erreur personnalisé
     * @return bool True si valide
     */
    public function validateSiret(string $siret, string $field = 'siret', ?string $message = null): bool
    {
        // Supprime les espaces
        $siret = preg_replace('/\s/', '', $siret);
        
        // Doit contenir exactement 14 chiffres
        if (!preg_match('/^\d{14}$/', $siret)) {
            $this->addError($field, $message ?? $this->defaultMessages['siret']);
            return false;
        }
        
        // Validation par algorithme de Luhn
        $sum = 0;
        for ($i = 0; $i < 14; $i++) {
            $digit = (int) $siret[$i];
            if ($i % 2 === 0) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }
        
        $isValid = $sum % 10 === 0;
        
        if (!$isValid) {
            $this->addError($field, $message ?? $this->defaultMessages['siret']);
        }
        
        return $isValid;
    }
    
    // =========================================================================
    // VALIDATION DE FICHIERS
    // =========================================================================
    
    /**
     * Valide un fichier uploadé
     * 
     * @param array $file Données du fichier ($_FILES['field'])
     * @param array $options Options de validation
     * @param string $field Nom du champ
     * @return bool True si valide
     */
    public function validateFile(array $file, array $options = [], string $field = 'file'): bool
    {
        $defaults = [
            'required' => true,
            'max_size' => 5 * 1024 * 1024, // 5 Mo par défaut
            'allowed_types' => [], // Vide = tous les types
            'allowed_extensions' => [], // Vide = toutes les extensions
        ];
        
        $options = array_merge($defaults, $options);
        $isValid = true;
        
        // Vérifie si le fichier est présent
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            if ($options['required']) {
                $this->addError($field, $this->defaultMessages['file_required']);
                return false;
            }
            return true;
        }
        
        // Vérifie les erreurs d'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->addError($field, $this->getUploadErrorMessage($file['error']));
            return false;
        }
        
        // Vérifie la taille
        if ($file['size'] > $options['max_size']) {
            $maxMb = round($options['max_size'] / (1024 * 1024), 2);
            $this->addError($field, $this->formatMessage('file_size', ['max' => $maxMb]));
            $isValid = false;
        }
        
        // Vérifie le type MIME
        if (!empty($options['allowed_types'])) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            
            if (!in_array($mimeType, $options['allowed_types'])) {
                $this->addError($field, $this->defaultMessages['file_type']);
                $isValid = false;
            }
        }
        
        // Vérifie l'extension
        if (!empty($options['allowed_extensions'])) {
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($extension, array_map('strtolower', $options['allowed_extensions']))) {
                $this->addError($field, $this->defaultMessages['file_type']);
                $isValid = false;
            }
        }
        
        return $isValid;
    }
    
    /**
     * Valide une image uploadée
     * 
     * @param array $file Données du fichier ($_FILES['field'])
     * @param array $options Options de validation
     * @param string $field Nom du champ
     * @return bool True si valide
     */
    public function validateImage(array $file, array $options = [], string $field = 'image'): bool
    {
        $defaults = [
            'max_size' => 2 * 1024 * 1024, // 2 Mo
            'allowed_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'max_width' => null,
            'max_height' => null,
            'min_width' => null,
            'min_height' => null,
        ];
        
        $options = array_merge($defaults, $options);
        
        // Validation de base du fichier
        if (!$this->validateFile($file, $options, $field)) {
            return false;
        }
        
        // Vérifie si c'est une vraie image
        if (isset($file['tmp_name']) && $file['error'] === UPLOAD_ERR_OK) {
            $imageInfo = @getimagesize($file['tmp_name']);
            
            if ($imageInfo === false) {
                $this->addError($field, 'Le fichier n\'est pas une image valide.');
                return false;
            }
            
            [$width, $height] = $imageInfo;
            
            // Vérifie les dimensions
            if ($options['max_width'] !== null && $width > $options['max_width']) {
                $this->addError($field, "L'image ne doit pas dépasser {$options['max_width']} pixels de large.");
                return false;
            }
            
            if ($options['max_height'] !== null && $height > $options['max_height']) {
                $this->addError($field, "L'image ne doit pas dépasser {$options['max_height']} pixels de haut.");
                return false;
            }
            
            if ($options['min_width'] !== null && $width < $options['min_width']) {
                $this->addError($field, "L'image doit faire au moins {$options['min_width']} pixels de large.");
                return false;
            }
            
            if ($options['min_height'] !== null && $height < $options['min_height']) {
                $this->addError($field, "L'image doit faire au moins {$options['min_height']} pixels de haut.");
                return false;
            }
        }
        
        return true;
    }
    
    // =========================================================================
    // GESTION DES ERREURS
    // =========================================================================
    
    /**
     * Ajoute une erreur au tableau des erreurs
     * 
     * @param string $field Nom du champ en erreur
     * @param string $message Message d'erreur
     * @return self Instance pour chaînage
     */
    public function addError(string $field, string $message): self
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
        return $this;
    }
    
    /**
     * Vérifie s'il y a des erreurs
     * 
     * @return bool True s'il y a des erreurs
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
    
    /**
     * Vérifie si un champ spécifique a des erreurs
     * 
     * @param string $field Nom du champ
     * @return bool True si le champ a des erreurs
     */
    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]) && !empty($this->errors[$field]);
    }
    
    /**
     * Récupère toutes les erreurs
     * 
     * @return array Tableau des erreurs [field => [messages]]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    /**
     * Récupère les erreurs d'un champ spécifique
     * 
     * @param string $field Nom du champ
     * @return array Messages d'erreur du champ
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }
    
    /**
     * Récupère la première erreur d'un champ
     * 
     * @param string $field Nom du champ
     * @return string|null Premier message d'erreur ou null
     */
    public function getFirstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }
    
    /**
     * Récupère toutes les erreurs sous forme de liste plate
     * 
     * @return array Liste de tous les messages d'erreur
     */
    public function getAllErrorMessages(): array
    {
        $messages = [];
        foreach ($this->errors as $fieldErrors) {
            $messages = array_merge($messages, $fieldErrors);
        }
        return $messages;
    }
    
    /**
     * Récupère les erreurs formatées pour l'affichage HTML
     * 
     * @param string $wrapper Balise wrapper (ul, div, etc.)
     * @param string $item Balise item (li, p, etc.)
     * @return string HTML des erreurs
     */
    public function getErrorsHtml(string $wrapper = 'ul', string $item = 'li'): string
    {
        if (!$this->hasErrors()) {
            return '';
        }
        
        $html = "<{$wrapper} class=\"validation-errors\">";
        foreach ($this->getAllErrorMessages() as $message) {
            $html .= "<{$item}>" . htmlspecialchars($message) . "</{$item}>";
        }
        $html .= "</{$wrapper}>";
        
        return $html;
    }
    
    // =========================================================================
    // MÉTHODES UTILITAIRES
    // =========================================================================
    
    /**
     * Formate un message d'erreur avec des placeholders
     * 
     * @param string $key Clé du message dans defaultMessages
     * @param array $replacements Valeurs de remplacement [:field => 'Nom']
     * @return string Message formaté
     */
    private function formatMessage(string $key, array $replacements = []): string
    {
        $message = $this->defaultMessages[$key] ?? $key;
        
        foreach ($replacements as $placeholder => $value) {
            $message = str_replace(':' . $placeholder, (string) $value, $message);
        }
        
        return $message;
    }
    
    /**
     * Récupère le message d'erreur d'upload PHP
     * 
     * @param int $errorCode Code d'erreur PHP
     * @return string Message d'erreur en français
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la taille maximale autorisée par le serveur.',
            UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille maximale autorisée par le formulaire.',
            UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement téléchargé.',
            UPLOAD_ERR_NO_TMP_DIR => 'Le dossier temporaire est manquant.',
            UPLOAD_ERR_CANT_WRITE => 'Échec de l\'écriture du fichier sur le disque.',
            UPLOAD_ERR_EXTENSION => 'Une extension PHP a arrêté le téléchargement.',
            default => 'Une erreur inconnue s\'est produite lors du téléchargement.',
        };
    }
    
    /**
     * Définit une donnée validée
     * 
     * @param string $key Nom de la donnée
     * @param mixed $value Valeur validée
     * @return self Instance pour chaînage
     */
    public function setValidated(string $key, mixed $value): self
    {
        $this->validated[$key] = $value;
        return $this;
    }
    
    /**
     * Récupère toutes les données validées
     * 
     * @return array Données validées
     */
    public function getValidated(): array
    {
        return $this->validated;
    }
    
    /**
     * Récupère une donnée validée spécifique
     * 
     * @param string $key Nom de la donnée
     * @param mixed $default Valeur par défaut
     * @return mixed Valeur validée ou défaut
     */
    public function getValidatedValue(string $key, mixed $default = null): mixed
    {
        return $this->validated[$key] ?? $default;
    }
}
