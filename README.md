# RZX.bio Conversion Tracking

Plugin WordPress per tracciare le conversioni WooCommerce e attribuire i ricavi ai tuoi link RZX.bio automaticamente.

## Requisiti

- WordPress 5.8+
- WooCommerce 6.0+
- PHP 7.4+

## Installazione

### Metodo 1: Download dalla Release

1. Vai su [Releases](../../releases)
2. Scarica `rzx-conversion-tracking.zip` dall'ultima release
3. In WordPress, vai su **Plugin → Aggiungi nuovo → Carica plugin**
4. Seleziona il file zip e clicca **Installa ora**
5. Attiva il plugin

### Metodo 2: Upload manuale

1. Scarica o clona questo repository
2. Copia la cartella `rzx-conversion-tracking` in `/wp-content/plugins/`
3. Attiva il plugin dal pannello WordPress

## Configurazione

1. Vai su **WooCommerce → Impostazioni → RZX.bio**
2. Inserisci la tua **API Key** (disponibile nella dashboard RZX.bio)
3. Clicca **Test Connection** per verificare
4. Abilita il tracking e salva

## Come funziona

```
Visitatore clicca link RZX.bio
         ↓
tuosito.com/prodotto?_rzx=12345
         ↓
Plugin salva ID in cookie (30 giorni)
         ↓
Visitatore completa acquisto
         ↓
Plugin invia conversione a RZX.bio
         ↓
Revenue attribuito al link corretto
```

## Impostazioni disponibili

| Impostazione | Descrizione |
|--------------|-------------|
| API Key | Chiave API RZX.bio (obbligatoria) |
| Enable Tracking | Attiva/disattiva il tracciamento |
| Track Product Details | Invia dettagli prodotti nei metadata |
| Attribution Window | Finestra di attribuzione (24h / 7gg / 30gg) |
| Debug Mode | Log in WooCommerce → Status → Logs |

## Compatibilità

- ✅ HPOS (High-Performance Order Storage)
- ✅ Checkout Blocks
- ✅ Classic Checkout
- ✅ PHP 8.0 - 8.3
- ✅ WordPress 6.7
- ✅ WooCommerce 9.5

## Struttura file

```
rzx-conversion-tracking/
├── rzx-conversion-tracking.php       # File principale
├── includes/
│   ├── class-rzx-cookie-handler.php  # Gestione cookie
│   ├── class-rzx-settings.php        # Pagina impostazioni
│   └── class-rzx-tracker.php         # Invio conversioni
├── assets/
│   ├── css/admin.css
│   └── js/admin.js
├── languages/
│   └── rzx-conversion-tracking.pot
└── readme.txt
```

## Hook per sviluppatori

```php
// Modificare payload prima dell'invio
add_filter('rzx_conversion_payload', function($payload, $order) {
    // modifica $payload
    return $payload;
}, 10, 2);

// Azione dopo invio riuscito
add_action('rzx_conversion_sent', function($order, $response, $conversion_id) {
    // fai qualcosa
}, 10, 3);
```

## Changelog

### 1.1.0
- Supporto completo HPOS e Block Checkout
- Attribution window configurabile
- Metadata estesi
- Miglioramenti sicurezza cookie

### 1.0.0
- Release iniziale

## Licenza

GPL v2 or later
