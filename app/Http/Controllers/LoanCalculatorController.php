<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use \Zwei\LoanCalculator\PaymentCalculatorFactory;

use Validator;

class LoanCalculatorController extends Controller
{
    const MONTHS = 'MONTHS';
    const YEARS = 'YEARS';
    public function __construct()
    {

    }

    public function calculate(Request $request) {

        $rules = [
            'principal' => 'required|min:2',
            'currency' => 'required|min:3',
            'repayment_period' => 'required|in:MONTHS,YEARS',
            'repayment_amount' => 'required|min:1',
            'repayment_type' => 'required|in:1,2,3,4',
            'interest_period' => 'required|in:MONTHS,YEARS',
            'interest_amount' => 'required|min:1',
            'down_payment_type' => 'nullable|in:PERCENTAGE,AMOUNT',
            'down_payment_amount' => 'nullable|min:1',
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()) {
            $errors = $validator->errors();
            return response()->json(['errors' => $errors->toArray(), 'data' => null]);
        }

        try {

            $principal = $request->get('principal', '0');
            if(!is_null($downPaymentType = $request->get('down_payment_type'))) {
                $downPaymentAmount = $request->get('down_payment_amount', 0);
                $principal = $downPaymentType == 'PERCENTAGE'? $principal - ($principal * $downPaymentAmount / 100): $principal - $downPaymentAmount;
            }

            $currency = $request->get('currency', 'usd');
            $repaymentPeriod = $request->get('repayment_period');
            $repaymentAmount = $request->get('repayment_amount');
            $repaymentType = $request->get('repayment_type');
            $months = $repaymentPeriod == self::YEARS? $repaymentAmount * 12: $repaymentAmount;
            $interestPeriod = $request->get('interest_period');
            $interestAmount = $request->get('interest_amount');
            $yearInterestRate = $interestPeriod == self::YEARS? ($interestAmount / 100) : ($interestAmount * 12 / 100);
            $time = Carbon::now()->getTimestamp();
            $decimalDigits = 2;
            $object = PaymentCalculatorFactory::getPaymentCalculatorObj($repaymentType, $principal, $yearInterestRate, $months, $time, $decimalDigits);
            $repayments = $object->getPlanLists();
            $repaymentList = [];
            $totalPrincipal = 0;
            $totalInterest = 0;
            $totalRepayment = 0;

            if(!is_null($repayments)) {
                foreach ($repayments as $key =>  $repayment) {
                    $result = [];
                    $date  = Carbon::parse($repayment['time']);
                    $result['period'] = $repayment['period'];
                    $result['payment_at'] = $date->format('d-m-yy');
                    $result['payment_amount'] = $repayment['total_money'];
                    $result['principal'] = $repayment['principal'];
                    $result['interest'] = $repayment['interest'];
                    $result['remain_principal'] = $repayment['remain_principal'];
                    $result['remain_interest'] = $repayment['remain_interest'];
                    $totalRepayment +=  $repayment['total_money'];
                    $totalInterest +=  $repayment['interest'];
                    $totalPrincipal +=  $repayment['principal'];
                    $repaymentList[] = $result;
                }
            }

            return response()->json([
                'data' => [
                    'currency' => $currency,
                    'principal' => $principal,
                    'total_interest' => $totalInterest,
                    'total_principal' => $totalPrincipal,
                    'total_months' => $months,
                    'total_repayment' => $totalRepayment,
                    'repayments' => $repaymentList,
                ],
                'errors' => null,
            ]);

        } catch (\Exception $exception) {

            return response()->json([
                'data' => null,
                'errors' => [
                    'exception'  => $exception->getMessage(),
                ],
            ]);

        }
    }

}
