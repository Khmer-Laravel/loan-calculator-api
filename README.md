
# Loan Calculator API

## Example 

![Preview](https://raw.githubusercontent.com/phuongphally/loan-calculator-api/master/previews/example.png)


## Install
 ``composer install``
 
## Database 
 ```php artisan migrate:refresh --seed```  
 
## Run Project 

````php artisan serve```` 
 
## API  URL 

 ```http://127.0.0.1:8000/api/loan/calculate``` 
 
## Available param

 ````http://127.0.0.1:8000/api/loan/calculate?principal=100000&currency=USD&repayment_period=MONTHS&repayment_amount=36&repayment_type=2&interest_period=YEARS&interest_amount=12&down_payment_type=PERCENTAGE&down_payment_amount=20````  

