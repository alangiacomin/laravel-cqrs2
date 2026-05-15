# laravel-cqrs

Semplice pacchetto per organizzare il pattern CQRS in applicazioni Laravel. Fornisce una struttura minima per Commands,
Queries e relativi handler, facilitando la separazione tra operazioni di scrittura e lettura.

## Installazione

Installa il pacchetto con Composer:

    composer require alangiacomin/laravel-cqrs

(Se il pacchetto non usa auto-discovery, registrare il service provider nel file `config/app.php`.)

## Uso rapido

- Creare Command e Query nelle rispettive cartelle (es. app/Commands, app/Queries).
- Implementare gli handler per gestire la logica (es. app/Handlers).
- Dispatchare comandi e query tramite il bus fornito dal pacchetto (es. Bus::dispatch(...)).

Questo README è volutamente minimale: per esempi dettagliati e integrazione nell'applicazione, consultare il codice del
pacchetto.

## Applicazione base pronta all'uso


