# CRM Pro - Setup Guide

## Requisiti
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+

## Installazione

### 1. Configurazione Database
Copia `.env.example` in `.env` e configura:
```
DB_DATABASE=crm_pro
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 2. Microsoft Outlook Integration
Nel file `.env` configura:
```
MICROSOFT_CLIENT_ID=your_azure_app_client_id
MICROSOFT_CLIENT_SECRET=your_azure_app_client_secret  
MICROSOFT_TENANT_ID=common
MICROSOFT_REDIRECT_URI=https://tuodominio.it/outlook/callback
```

#### Come ottenere le credenziali Azure:
1. Vai su https://portal.azure.com
2. Registra una nuova app in "App registrations"
3. Aggiungi i permessi: `Mail.Read`, `Mail.Send`, `Calendars.Read`, `offline_access`
4. Aggiungi il Redirect URI: `https://tuodominio.it/outlook/callback`
5. Copia Client ID e crea un Client Secret

### 3. Avvio
```bash
composer install
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

### 4. Credenziali default
- **Admin**: admin@crm.local / password
- **Manager**: manager@crm.local / password
- **Agente**: agente@crm.local / password

## Ruoli e Permessi

| Funzionalità | Admin | Manager | Agente |
|---|---|---|---|
| Tutti i clienti | ✅ | ✅ | ❌ (solo assegnati) |
| Creare clienti | ✅ | ✅ | ✅ |
| Eliminare clienti | ✅ | ✅ | ❌ |
| Gestire utenti | ✅ | ❌ | ❌ |
| Tutte le attività | ✅ | ✅ | ❌ (solo proprie) |
| Pipeline | ✅ | ✅ | ✅ |

## Funzionalità principali

### 📊 Dashboard
- Statistiche in tempo reale (clienti, attività, task)
- Grafico attività ultimi 7 giorni
- Clienti recenti con ultima interazione
- Task in sospeso e scaduti
- Valore pipeline attivo

### 👥 Clienti
- Anagrafica completa (nome, contatti, azienda, LinkedIn, etc.)
- Stato: Lead, Prospect, Attivo, Inattivo, Perso
- Priorità: Bassa, Media, Alta
- Tag personalizzabili con colori
- Assegnazione a utenti
- Valore annuo stimato

### 📋 Timeline Cliente
- Tutte le interazioni in ordine cronologico
- Tipi: Email, Chiamata, WhatsApp, SMS, Meeting, Nota
- Direzione: In entrata / In uscita
- Allegati per ogni attività

### ✅ Task & Reminder
- Tipi: Follow-up, Chiamata, Email, Meeting, Altro
- Priorità: Bassa, Media, Alta, Urgente
- Data scadenza + reminder
- Assegnazione a colleghi
- Notifica automatica alla scadenza

### 📧 Integrazione Outlook
- OAuth2 con Microsoft Azure
- Sincronizzazione automatica email in entrata
- Matching automatico email → cliente (per indirizzo email)
- Email appare nella timeline del cliente
- Possibilità di rispondere dal CRM

## Scheduler (per reminder)
Aggiungi al crontab del server:
```
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```
