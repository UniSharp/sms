# SMS package for Laravel

Only support APTG SMS for now.

## Installation

```
    composer require unisharp/sms
```

## Usage

### Using facade

Edit `.env`:

```
    APTG_MDN=xxx
    APTG_UID=yyy
    APTG_UPASS=zzz
    SMS_IS_DRY_RUN=false // If set to true, no sms will be sent. Only log messages are recorded.
```

Code: 

```
    \Sms::send($phone_number, $message); // boolean value is returned.
```

### Pass credentials manually

```
    $client = new AptgSmsClient('your APTG_MDN', 'your APTG_UID', 'your APTG_UPASS');

    $response = $client->send([$phone_number], $message);  // Instance of AptgClient is returned.
```
