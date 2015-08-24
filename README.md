# EAI Wiki

http://interlab.ait.asia/eai-wiki/


# eaifilter
EAI filter for postfix 

This filter check for the receiving mail server if it supports SMTPUTF8, if it does not support it will rewrite the FROM Address with ASCII only that is aliased to the sender email.


# How to use

Copy eaifilter.php in /etc/postfix 
Change to root 


# master.cf

```
smtp      inet  n       -       n       -       -       smtpd  -o content_filter=eaifilter -o receive_override_options=no_header_body_checks
eaifilter unix - n n - - pipe flags=RXhu user=nobody argv=/etc/postfix/eaifilter.php -f ${sender} -d ${recipient}
127.0.0.1:10025   inet  n       -       n       -       -        smtpd
        -o content_filter=
        -o receive_override_options=no_unknown_recipient_checks,no_header_body_checks,no_milters
        -o smtpd_helo_required=no
        -o smtpd_helo_restrictions=
        -o smtpd_data_restrictions=
        -o smtpd_client_restrictions=
        -o smtpd_sender_restrictions=
        -o smtpd_recipient_restrictions=permit_mynetworks,reject
        -o mynetworks_style=host
        -o in_flow_delay=0
```

# main.cf

``` 
eaifilter_destination_recipient_limit=1
```

