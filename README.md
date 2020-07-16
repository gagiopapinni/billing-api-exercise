# billing-api-exercise


## quick api overview

* /register (POST)  
   parameters: target-payment target name(string), amount-transaction value(int)    
   registers a payment to be confirmed, redirects to confirmation page i.e /payments/card/form?sessionID=...  
* /payments/card/form (GET)  
   parameters: sessionID  
   returns a transaction page with confirmation form for a registered payment  
   form submit sends data to /payments/card/transaction   
* /payments/card/transaction (POST)  
   parameters: owner, cvv, cardNumber, expirationDate, sessionID  
   checks card data in order to confirm transaction   
   If 30 mins have passed since payment registration, confirmation will fail  
   Otherwise if card number satisfies the Luhn algorithm, confirmation succeeds  
* /payments/card/transactions (GET)  
   parameters: from, to - UNIX timestamps  
   returns json object with registered transactions  
   
  
