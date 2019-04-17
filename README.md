# Buto-Plugin-PaymentmanagerClient

Handle payment flow in a website. Restrict element content by session params for multiple services.

Service flow (exemple with service id service_001):
- Has service and all is fine, the service is in period and there is no need of payment.
- Has service but service has to be paid, the period is soon out dated. 
- Has no service, could not find any service in period and a payment is required.

## Settings
```
plugin:
  paymentmanager:
    client:
      enabled: true
      settings:
        mysql: 'yml:/../buto_data/theme/[theme]/mysql.yml'
        customer_session_tag: _session_path_to_customer_tag_
        url_payment: '/p/payment'
        swish_number: _Swish_number_to_display_
        renew_days: 20
```
Set service in session.
```
events:
  signin:
    -
      plugin: 'paymentmanager/client'
      method: 'signin'
```

## Service info page
Show info about service_001.
```
content:
  -
    type: widget
    data:
      plugin: paymentmanager/client
      method: service
      data:
        service: service_001
```
## Service restrict page
Redirect to Payment page when service is out of date. Show info when service has to be paid.
```
type: widget
data:
  plugin: paymentmanager/client
  method: service
  data:
    service: service_001
    no_service: true
```

## Payment page
/p/payment?service_id=service_001
```
settings:
  layout:
    - html
    - main
content:
  -
    type: h1
    innerHTML: Service
  -
    type: p
    innerHTML: This service has to be paid...
  -
    type: widget
    settings:
      role:
        item:
          - client
    data:
      plugin: paymentmanager/client
      method: payment
```



## Element
Restrict element content.
```
type: div
settings:
  enabled: globals:_SESSION/plugin/paymentmanager/client/service/service_001/id
innerHTML: This content must have active service with id service_001.
```

## Schema file
```
/mysql/schema.yml
```
