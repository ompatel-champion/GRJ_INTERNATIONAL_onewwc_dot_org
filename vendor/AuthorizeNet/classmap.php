<?php
spl_autoload_extensions(".php"); // comma-separated list
spl_autoload_register();

/**
 * A map of classname => filename for SPL autoloading.
 *
 * @package AuthorizeNet
 */

$baseDir = __DIR__ ;
$libDir    = $baseDir . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR;

return array(

    // Following section contains the new controller model classes needed
    //Utils
    //'net\authorize\util\ObjectToXml' => $libDir . 'net/authorize/util/ObjectToXml.php',
    'net\authorize\util\HttpClient' => $libDir . 'net/authorize/util/HttpClient.php',
    'net\authorize\util\Helpers' => $libDir . 'net/authorize/util/Helpers.php',
    'net\authorize\util\Log' => $libDir . 'net/authorize/util/Log.php',
    'net\authorize\util\LogFactory' => $libDir . 'net/authorize/util/LogFactory.php',
    'net\authorize\util\ANetSensitiveFields' => $libDir . 'net/authorize/util/ANetSensitiveFields.php',
    'net\authorize\util\SensitiveTag' => $libDir . 'net/authorize/util/SensitiveTag.php',
	'net\authorize\util\SensitiveDataConfigType' => $libDir . 'net/authorize/util/SensitiveDataConfigType.php',
	'net\authorize\util\Mapper' => $libDir . 'net/authorize/util/Mapper.php',
	'net\authorize\util\MapperObj' => $libDir . 'net/authorize/util/MapperObj.php',

    //constants
    'net\authorize\api\constants\ANetEnvironment' => $libDir . 'net/authorize/api/constants/ANetEnvironment.php',

    //base classes
    'net\authorize\api\controller\base\IApiOperation' => $libDir . 'net/authorize/api/controller/base/IApiOperation.php',
    'net\authorize\api\controller\base\ApiOperationBase' => $libDir . 'net/authorize/api/controller/base/ApiOperationBase.php',

    //following are generated class mappings
    'net\authorize\api\contract\v1\ANetApiRequestType' => $libDir . 'net/authorize/api/contract/v1/ANetApiRequestType.php',
    'net\authorize\api\contract\v1\ANetApiResponseType' => $libDir . 'net/authorize/api/contract/v1/ANetApiResponseType.php',
    'net\authorize\api\contract\v1\ARBCancelSubscriptionRequest' => $libDir . 'net/authorize/api/contract/v1/ARBCancelSubscriptionRequest.php',
    'net\authorize\api\contract\v1\ARBCancelSubscriptionResponse' => $libDir . 'net/authorize/api/contract/v1/ARBCancelSubscriptionResponse.php',
    'net\authorize\api\contract\v1\ARBCreateSubscriptionRequest' => $libDir . 'net/authorize/api/contract/v1/ARBCreateSubscriptionRequest.php',
    'net\authorize\api\contract\v1\ARBCreateSubscriptionResponse' => $libDir . 'net/authorize/api/contract/v1/ARBCreateSubscriptionResponse.php',
    'net\authorize\api\contract\v1\ARBGetSubscriptionListRequest' => $libDir . 'net/authorize/api/contract/v1/ARBGetSubscriptionListRequest.php',
    'net\authorize\api\contract\v1\ARBGetSubscriptionListResponse' => $libDir . 'net/authorize/api/contract/v1/ARBGetSubscriptionListResponse.php',
    'net\authorize\api\contract\v1\ARBGetSubscriptionListSortingType' => $libDir . 'net/authorize/api/contract/v1/ARBGetSubscriptionListSortingType.php',
    'net\authorize\api\contract\v1\ARBGetSubscriptionRequest' => $libDir . 'net/authorize/api/contract/v1/ARBGetSubscriptionRequest.php',
    'net\authorize\api\contract\v1\ARBGetSubscriptionResponse' => $libDir . 'net/authorize/api/contract/v1/ARBGetSubscriptionResponse.php',
    'net\authorize\api\contract\v1\ARBGetSubscriptionStatusRequest' => $libDir . 'net/authorize/api/contract/v1/ARBGetSubscriptionStatusRequest.php',
    'net\authorize\api\contract\v1\ARBGetSubscriptionStatusResponse' => $libDir . 'net/authorize/api/contract/v1/ARBGetSubscriptionStatusResponse.php',
    'net\authorize\api\contract\v1\ARBSubscriptionMaskedType' => $libDir . 'net/authorize/api/contract/v1/ARBSubscriptionMaskedType.php',
    'net\authorize\api\contract\v1\ARBSubscriptionType' => $libDir . 'net/authorize/api/contract/v1/ARBSubscriptionType.php',
    'net\authorize\api\contract\v1\ArbTransactionType' => $libDir . 'net/authorize/api/contract/v1/ArbTransactionType.php',
    'net\authorize\api\contract\v1\ARBUpdateSubscriptionRequest' => $libDir . 'net/authorize/api/contract/v1/ARBUpdateSubscriptionRequest.php',
    'net\authorize\api\contract\v1\ARBUpdateSubscriptionResponse' => $libDir . 'net/authorize/api/contract/v1/ARBUpdateSubscriptionResponse.php',
    'net\authorize\api\contract\v1\ArrayOfSettingType' => $libDir . 'net/authorize/api/contract/v1/ArrayOfSettingType.php',
    'net\authorize\api\contract\v1\AuthenticateTestRequest' => $libDir . 'net/authorize/api/contract/v1/AuthenticateTestRequest.php',
    'net\authorize\api\contract\v1\AuthenticateTestResponse' => $libDir . 'net/authorize/api/contract/v1/AuthenticateTestResponse.php',
    'net\authorize\api\contract\v1\BankAccountMaskedType' => $libDir . 'net/authorize/api/contract/v1/BankAccountMaskedType.php',
    'net\authorize\api\contract\v1\BankAccountType' => $libDir . 'net/authorize/api/contract/v1/BankAccountType.php',
    'net\authorize\api\contract\v1\BatchDetailsType' => $libDir . 'net/authorize/api/contract/v1/BatchDetailsType.php',
    'net\authorize\api\contract\v1\BatchStatisticType' => $libDir . 'net/authorize/api/contract/v1/BatchStatisticType.php',
    'net\authorize\api\contract\v1\CardArtType' => $libDir . 'net/authorize/api/contract/v1/CardArtType.php',
    'net\authorize\api\contract\v1\CcAuthenticationType' => $libDir . 'net/authorize/api/contract/v1/CcAuthenticationType.php',
    'net\authorize\api\contract\v1\CreateCustomerPaymentProfileRequest' => $libDir . 'net/authorize/api/contract/v1/CreateCustomerPaymentProfileRequest.php',
    'net\authorize\api\contract\v1\CreateCustomerPaymentProfileResponse' => $libDir . 'net/authorize/api/contract/v1/CreateCustomerPaymentProfileResponse.php',
    'net\authorize\api\contract\v1\CreateCustomerProfileFromTransactionRequest' => $libDir . 'net/authorize/api/contract/v1/CreateCustomerProfileFromTransactionRequest.php',
    'net\authorize\api\contract\v1\CreateCustomerProfileRequest' => $libDir . 'net/authorize/api/contract/v1/CreateCustomerProfileRequest.php',
    'net\authorize\api\contract\v1\CreateCustomerProfileResponse' => $libDir . 'net/authorize/api/contract/v1/CreateCustomerProfileResponse.php',
    'net\authorize\api\contract\v1\CreateCustomerProfileTransactionRequest' => $libDir . 'net/authorize/api/contract/v1/CreateCustomerProfileTransactionRequest.php',
    'net\authorize\api\contract\v1\CreateCustomerProfileTransactionResponse' => $libDir . 'net/authorize/api/contract/v1/CreateCustomerProfileTransactionResponse.php',
    'net\authorize\api\contract\v1\CreateCustomerShippingAddressRequest' => $libDir . 'net/authorize/api/contract/v1/CreateCustomerShippingAddressRequest.php',
    'net\authorize\api\contract\v1\CreateCustomerShippingAddressResponse' => $libDir . 'net/authorize/api/contract/v1/CreateCustomerShippingAddressResponse.php',
    'net\authorize\api\contract\v1\CreateProfileResponseType' => $libDir . 'net/authorize/api/contract/v1/CreateProfileResponseType.php',
    'net\authorize\api\contract\v1\CreateTransactionRequest' => $libDir . 'net/authorize/api/contract/v1/CreateTransactionRequest.php',
    'net\authorize\api\contract\v1\CreateTransactionResponse' => $libDir . 'net/authorize/api/contract/v1/CreateTransactionResponse.php',
    'net\authorize\api\contract\v1\CreditCardMaskedType' => $libDir . 'net/authorize/api/contract/v1/CreditCardMaskedType.php',
    'net\authorize\api\contract\v1\CreditCardSimpleType' => $libDir . 'net/authorize/api/contract/v1/CreditCardSimpleType.php',
    'net\authorize\api\contract\v1\CreditCardTrackType' => $libDir . 'net/authorize/api/contract/v1/CreditCardTrackType.php',
    'net\authorize\api\contract\v1\CreditCardType' => $libDir . 'net/authorize/api/contract/v1/CreditCardType.php',
    'net\authorize\api\contract\v1\CustomerAddressExType' => $libDir . 'net/authorize/api/contract/v1/CustomerAddressExType.php',
    'net\authorize\api\contract\v1\CustomerAddressType' => $libDir . 'net/authorize/api/contract/v1/CustomerAddressType.php',
    'net\authorize\api\contract\v1\CustomerDataType' => $libDir . 'net/authorize/api/contract/v1/CustomerDataType.php',
	'net\authorize\api\contract\v1\CustomerProfileIdType' => $libDir . 'net/authorize/api/contract/v1/CustomerProfileIdType.php',
    'net\authorize\api\contract\v1\CustomerPaymentProfileBaseType' => $libDir . 'net/authorize/api/contract/v1/CustomerPaymentProfileBaseType.php',
    'net\authorize\api\contract\v1\CustomerPaymentProfileExType' => $libDir . 'net/authorize/api/contract/v1/CustomerPaymentProfileExType.php',
    'net\authorize\api\contract\v1\CustomerPaymentProfileListItemType' => $libDir . 'net/authorize/api/contract/v1/CustomerPaymentProfileListItemType.php',
    'net\authorize\api\contract\v1\CustomerPaymentProfileMaskedType' => $libDir . 'net/authorize/api/contract/v1/CustomerPaymentProfileMaskedType.php',
    'net\authorize\api\contract\v1\CustomerPaymentProfileSortingType' => $libDir . 'net/authorize/api/contract/v1/CustomerPaymentProfileSortingType.php',
    'net\authorize\api\contract\v1\CustomerPaymentProfileType' => $libDir . 'net/authorize/api/contract/v1/CustomerPaymentProfileType.php',
    'net\authorize\api\contract\v1\CustomerProfileBaseType' => $libDir . 'net/authorize/api/contract/v1/CustomerProfileBaseType.php',
    'net\authorize\api\contract\v1\CustomerProfileExType' => $libDir . 'net/authorize/api/contract/v1/CustomerProfileExType.php',
    'net\authorize\api\contract\v1\CustomerProfileMaskedType' => $libDir . 'net/authorize/api/contract/v1/CustomerProfileMaskedType.php',
    'net\authorize\api\contract\v1\CustomerProfilePaymentType' => $libDir . 'net/authorize/api/contract/v1/CustomerProfilePaymentType.php',
    'net\authorize\api\contract\v1\CustomerProfileSummaryType' => $libDir . 'net/authorize/api/contract/v1/CustomerProfileSummaryType.php',
    'net\authorize\api\contract\v1\CustomerProfileType' => $libDir . 'net/authorize/api/contract/v1/CustomerProfileType.php',
    'net\authorize\api\contract\v1\CustomerType' => $libDir . 'net/authorize/api/contract/v1/CustomerType.php',
    'net\authorize\api\contract\v1\DecryptPaymentDataRequest' => $libDir . 'net/authorize/api/contract/v1/DecryptPaymentDataRequest.php',
    'net\authorize\api\contract\v1\DecryptPaymentDataResponse' => $libDir . 'net/authorize/api/contract/v1/DecryptPaymentDataResponse.php',
    'net\authorize\api\contract\v1\DeleteCustomerPaymentProfileRequest' => $libDir . 'net/authorize/api/contract/v1/DeleteCustomerPaymentProfileRequest.php',
    'net\authorize\api\contract\v1\DeleteCustomerPaymentProfileResponse' => $libDir . 'net/authorize/api/contract/v1/DeleteCustomerPaymentProfileResponse.php',
    'net\authorize\api\contract\v1\DeleteCustomerProfileRequest' => $libDir . 'net/authorize/api/contract/v1/DeleteCustomerProfileRequest.php',
    'net\authorize\api\contract\v1\DeleteCustomerProfileResponse' => $libDir . 'net/authorize/api/contract/v1/DeleteCustomerProfileResponse.php',
    'net\authorize\api\contract\v1\DeleteCustomerShippingAddressRequest' => $libDir . 'net/authorize/api/contract/v1/DeleteCustomerShippingAddressRequest.php',
    'net\authorize\api\contract\v1\DeleteCustomerShippingAddressResponse' => $libDir . 'net/authorize/api/contract/v1/DeleteCustomerShippingAddressResponse.php',
    'net\authorize\api\contract\v1\DriversLicenseMaskedType' => $libDir . 'net/authorize/api/contract/v1/DriversLicenseMaskedType.php',
    'net\authorize\api\contract\v1\DriversLicenseType' => $libDir . 'net/authorize/api/contract/v1/DriversLicenseType.php',
    'net\authorize\api\contract\v1\EmailSettingsType' => $libDir . 'net/authorize/api/contract/v1/EmailSettingsType.php',
    'net\authorize\api\contract\v1\EncryptedTrackDataType' => $libDir . 'net/authorize/api/contract/v1/EncryptedTrackDataType.php',
    'net\authorize\api\contract\v1\EnumCollection' => $libDir . 'net/authorize/api/contract/v1/EnumCollection.php',
    'net\authorize\api\contract\v1\ErrorResponse' => $libDir . 'net/authorize/api/contract/v1/ErrorResponse.php',
    'net\authorize\api\contract\v1\ExtendedAmountType' => $libDir . 'net/authorize/api/contract/v1/ExtendedAmountType.php',
    'net\authorize\api\contract\v1\FDSFilterType' => $libDir . 'net/authorize/api/contract/v1/FDSFilterType.php',
    'net\authorize\api\contract\v1\FingerPrintType' => $libDir . 'net/authorize/api/contract/v1/FingerPrintType.php',
    'net\authorize\api\contract\v1\FraudInformationType'=> $libDir . 'net/authorize/api/contract/v1/FraudInformationType.php',
    'net\authorize\api\contract\v1\GetBatchStatisticsRequest' => $libDir . 'net/authorize/api/contract/v1/GetBatchStatisticsRequest.php',
    'net\authorize\api\contract\v1\GetBatchStatisticsResponse' => $libDir . 'net/authorize/api/contract/v1/GetBatchStatisticsResponse.php',
    'net\authorize\api\contract\v1\GetCustomerPaymentProfileListRequest' => $libDir . 'net/authorize/api/contract/v1/GetCustomerPaymentProfileListRequest.php',
    'net\authorize\api\contract\v1\GetCustomerPaymentProfileListResponse' => $libDir . 'net/authorize/api/contract/v1/GetCustomerPaymentProfileListResponse.php',
    'net\authorize\api\contract\v1\GetCustomerPaymentProfileRequest' => $libDir . 'net/authorize/api/contract/v1/GetCustomerPaymentProfileRequest.php',
    'net\authorize\api\contract\v1\GetCustomerPaymentProfileResponse' => $libDir . 'net/authorize/api/contract/v1/GetCustomerPaymentProfileResponse.php',
    'net\authorize\api\contract\v1\GetCustomerProfileIdsRequest' => $libDir . 'net/authorize/api/contract/v1/GetCustomerProfileIdsRequest.php',
    'net\authorize\api\contract\v1\GetCustomerProfileIdsResponse' => $libDir . 'net/authorize/api/contract/v1/GetCustomerProfileIdsResponse.php',
    'net\authorize\api\contract\v1\GetCustomerProfileRequest' => $libDir . 'net/authorize/api/contract/v1/GetCustomerProfileRequest.php',
    'net\authorize\api\contract\v1\GetCustomerProfileResponse' => $libDir . 'net/authorize/api/contract/v1/GetCustomerProfileResponse.php',
    'net\authorize\api\contract\v1\GetCustomerShippingAddressRequest' => $libDir . 'net/authorize/api/contract/v1/GetCustomerShippingAddressRequest.php',
    'net\authorize\api\contract\v1\GetCustomerShippingAddressResponse' => $libDir . 'net/authorize/api/contract/v1/GetCustomerShippingAddressResponse.php',
    'net\authorize\api\contract\v1\GetHostedPaymentPageRequest'=> $libDir . 'net/authorize/api/contract/v1/GetHostedPaymentPageRequest.php',
    'net\authorize\api\contract\v1\GetHostedPaymentPageResponse'=> $libDir . 'net/authorize/api/contract/v1/GetHostedPaymentPageResponse.php',
    'net\authorize\api\contract\v1\GetHostedProfilePageRequest' => $libDir . 'net/authorize/api/contract/v1/GetHostedProfilePageRequest.php',
    'net\authorize\api\contract\v1\GetHostedProfilePageResponse' => $libDir . 'net/authorize/api/contract/v1/GetHostedProfilePageResponse.php',
    'net\authorize\api\contract\v1\GetMerchantDetailsRequest'=> $libDir . 'net/authorize/api/contract/v1/GetMerchantDetailsRequest.php',
    'net\authorize\api\contract\v1\GetMerchantDetailsResponse'=> $libDir . 'net/authorize/api/contract/v1/GetMerchantDetailsResponse.php',
    'net\authorize\api\contract\v1\GetSettledBatchListRequest' => $libDir . 'net/authorize/api/contract/v1/GetSettledBatchListRequest.php',
    'net\authorize\api\contract\v1\GetSettledBatchListResponse' => $libDir . 'net/authorize/api/contract/v1/GetSettledBatchListResponse.php',
    'net\authorize\api\contract\v1\GetTransactionDetailsRequest' => $libDir . 'net/authorize/api/contract/v1/GetTransactionDetailsRequest.php',
    'net\authorize\api\contract\v1\GetTransactionDetailsResponse' => $libDir . 'net/authorize/api/contract/v1/GetTransactionDetailsResponse.php',
    'net\authorize\api\contract\v1\GetTransactionListRequest' => $libDir . 'net/authorize/api/contract/v1/GetTransactionListRequest.php',
    'net\authorize\api\contract\v1\GetTransactionListResponse' => $libDir . 'net/authorize/api/contract/v1/GetTransactionListResponse.php',
    'net\authorize\api\contract\v1\GetUnsettledTransactionListRequest' => $libDir . 'net/authorize/api/contract/v1/GetUnsettledTransactionListRequest.php',
    'net\authorize\api\contract\v1\GetUnsettledTransactionListResponse' => $libDir . 'net/authorize/api/contract/v1/GetUnsettledTransactionListResponse.php',
    'net\authorize\api\contract\v1\HeldTransactionRequestType'=> $libDir . 'net/authorize/api/contract/v1/HeldTransactionRequestType.php',
    'net\authorize\api\contract\v1\ImpersonationAuthenticationType' => $libDir . 'net/authorize/api/contract/v1/ImpersonationAuthenticationType.php',
    'net\authorize\api\contract\v1\IsAliveRequest' => $libDir . 'net/authorize/api/contract/v1/IsAliveRequest.php',
    'net\authorize\api\contract\v1\IsAliveResponse' => $libDir . 'net/authorize/api/contract/v1/IsAliveResponse.php',
    'net\authorize\api\contract\v1\KeyBlockType' => $libDir . 'net/authorize/api/contract/v1/KeyBlockType.php',
    'net\authorize\api\contract\v1\KeyManagementSchemeType' => $libDir . 'net/authorize/api/contract/v1/KeyManagementSchemeType.php',
    'net\authorize\api\contract\v1\KeyValueType' => $libDir . 'net/authorize/api/contract/v1/KeyValueType.php',
    'net\authorize\api\contract\v1\LineItemType' => $libDir . 'net/authorize/api/contract/v1/LineItemType.php',
    'net\authorize\api\contract\v1\LogoutRequest' => $libDir . 'net/authorize/api/contract/v1/LogoutRequest.php',
    'net\authorize\api\contract\v1\LogoutResponse' => $libDir . 'net/authorize/api/contract/v1/LogoutResponse.php',
    'net\authorize\api\contract\v1\MerchantAuthenticationType' => $libDir . 'net/authorize/api/contract/v1/MerchantAuthenticationType.php',
    'net\authorize\api\contract\v1\MerchantContactType' => $libDir . 'net/authorize/api/contract/v1/MerchantContactType.php',
    'net\authorize\api\contract\v1\MessagesType' => $libDir . 'net/authorize/api/contract/v1/MessagesType.php',
    'net\authorize\api\contract\v1\MobileDeviceLoginRequest' => $libDir . 'net/authorize/api/contract/v1/MobileDeviceLoginRequest.php',
    'net\authorize\api\contract\v1\MobileDeviceLoginResponse' => $libDir . 'net/authorize/api/contract/v1/MobileDeviceLoginResponse.php',
    'net\authorize\api\contract\v1\MobileDeviceRegistrationRequest' => $libDir . 'net/authorize/api/contract/v1/MobileDeviceRegistrationRequest.php',
    'net\authorize\api\contract\v1\MobileDeviceRegistrationResponse' => $libDir . 'net/authorize/api/contract/v1/MobileDeviceRegistrationResponse.php',
    'net\authorize\api\contract\v1\MobileDeviceType' => $libDir . 'net/authorize/api/contract/v1/MobileDeviceType.php',
    'net\authorize\api\contract\v1\NameAndAddressType' => $libDir . 'net/authorize/api/contract/v1/NameAndAddressType.php',
    'net\authorize\api\contract\v1\OpaqueDataType' => $libDir . 'net/authorize/api/contract/v1/OpaqueDataType.php',
    'net\authorize\api\contract\v1\OrderExType' => $libDir . 'net/authorize/api/contract/v1/OrderExType.php',
    'net\authorize\api\contract\v1\OrderType' => $libDir . 'net/authorize/api/contract/v1/OrderType.php',
    'net\authorize\api\contract\v1\PagingType' => $libDir . 'net/authorize/api/contract/v1/PagingType.php',
    'net\authorize\api\contract\v1\PaymentDetailsType' => $libDir . 'net/authorize/api/contract/v1/PaymentDetailsType.php',
    'net\authorize\api\contract\v1\PaymentMaskedType' => $libDir . 'net/authorize/api/contract/v1/PaymentMaskedType.php',
    'net\authorize\api\contract\v1\PaymentProfileType' => $libDir . 'net/authorize/api/contract/v1/PaymentProfileType.php',
    'net\authorize\api\contract\v1\PaymentScheduleType' => $libDir . 'net/authorize/api/contract/v1/PaymentScheduleType.php',
    'net\authorize\api\contract\v1\PaymentSimpleType' => $libDir . 'net/authorize/api/contract/v1/PaymentSimpleType.php',
    'net\authorize\api\contract\v1\PaymentType' => $libDir . 'net/authorize/api/contract/v1/PaymentType.php',
    'net\authorize\api\contract\v1\PayPalType' => $libDir . 'net/authorize/api/contract/v1/PayPalType.php',
    'net\authorize\api\contract\v1\PermissionType' => $libDir . 'net/authorize/api/contract/v1/PermissionType.php',
    'net\authorize\api\contract\v1\ProcessorType'=> $libDir . 'net/authorize/api/contract/v1/ProcessorType.php',
    'net\authorize\api\contract\v1\ProfileTransactionType' => $libDir . 'net/authorize/api/contract/v1/ProfileTransactionType.php',
    'net\authorize\api\contract\v1\ProfileTransAmountType' => $libDir . 'net/authorize/api/contract/v1/ProfileTransAmountType.php',
    'net\authorize\api\contract\v1\ProfileTransAuthCaptureType' => $libDir . 'net/authorize/api/contract/v1/ProfileTransAuthCaptureType.php',
    'net\authorize\api\contract\v1\ProfileTransAuthOnlyType' => $libDir . 'net/authorize/api/contract/v1/ProfileTransAuthOnlyType.php',
    'net\authorize\api\contract\v1\ProfileTransCaptureOnlyType' => $libDir . 'net/authorize/api/contract/v1/ProfileTransCaptureOnlyType.php',
    'net\authorize\api\contract\v1\ProfileTransOrderType' => $libDir . 'net/authorize/api/contract/v1/ProfileTransOrderType.php',
    'net\authorize\api\contract\v1\ProfileTransPriorAuthCaptureType' => $libDir . 'net/authorize/api/contract/v1/ProfileTransPriorAuthCaptureType.php',
    'net\authorize\api\contract\v1\ProfileTransRefundType' => $libDir . 'net/authorize/api/contract/v1/ProfileTransRefundType.php',
    'net\authorize\api\contract\v1\ProfileTransVoidType' => $libDir . 'net/authorize/api/contract/v1/ProfileTransVoidType.php',
    'net\authorize\api\contract\v1\ReturnedItemType' => $libDir . 'net/authorize/api/contract/v1/ReturnedItemType.php',
    'net\authorize\api\contract\v1\SearchCriteriaCustomerProfileType' => $libDir . 'net/authorize/api/contract/v1/SearchCriteriaCustomerProfileType.php',
	'net\authorize\api\contract\v1\SecurePaymentContainerErrorType' => $libDir . 'net/authorize/api/contract/v1/SecurePaymentContainerErrorType.php',
	'net\authorize\api\contract\v1\SecurePaymentContainerRequest' => $libDir . 'net/authorize/api/contract/v1/SecurePaymentContainerRequest.php',
	'net\authorize\api\contract\v1\SecurePaymentContainerResponse' => $libDir . 'net/authorize/api/contract/v1/SecurePaymentContainerResponse.php',
    'net\authorize\api\contract\v1\SendCustomerTransactionReceiptRequest' => $libDir . 'net/authorize/api/contract/v1/SendCustomerTransactionReceiptRequest.php',
    'net\authorize\api\contract\v1\SendCustomerTransactionReceiptResponse' => $libDir . 'net/authorize/api/contract/v1/SendCustomerTransactionReceiptResponse.php',
    'net\authorize\api\contract\v1\SettingType' => $libDir . 'net/authorize/api/contract/v1/SettingType.php',
    'net\authorize\api\contract\v1\SolutionType' => $libDir . 'net/authorize/api/contract/v1/SolutionType.php',
	'net\authorize\api\contract\v1\SubMerchantType' => $libDir . 'net/authorize/api/contract/v1/SubMerchantType.php',
    'net\authorize\api\contract\v1\SubscriptionCustomerProfileType' => $libDir . 'net/authorize/api/contract/v1/SubscriptionCustomerProfileType.php',
    'net\authorize\api\contract\v1\SubscriptionDetailType' => $libDir . 'net/authorize/api/contract/v1/SubscriptionDetailType.php',
    'net\authorize\api\contract\v1\SubscriptionPaymentType' => $libDir . 'net/authorize/api/contract/v1/SubscriptionPaymentType.php',
    'net\authorize\api\contract\v1\TokenMaskedType' => $libDir . 'net/authorize/api/contract/v1/TokenMaskedType.php',
    'net\authorize\api\contract\v1\TransactionDetailsType' => $libDir . 'net/authorize/api/contract/v1/TransactionDetailsType.php',
    'net\authorize\api\contract\v1\TransactionListSortingType'=> $libDir . 'net/authorize/api/contract/v1/TransactionListSortingType.php',
    'net\authorize\api\contract\v1\TransactionRequestType' => $libDir . 'net/authorize/api/contract/v1/TransactionRequestType.php',
    'net\authorize\api\contract\v1\TransactionResponseType' => $libDir . 'net/authorize/api/contract/v1/TransactionResponseType.php',
    'net\authorize\api\contract\v1\TransactionSummaryType' => $libDir . 'net/authorize/api/contract/v1/TransactionSummaryType.php',
    'net\authorize\api\contract\v1\TransRetailInfoType' => $libDir . 'net/authorize/api/contract/v1/TransRetailInfoType.php',
    'net\authorize\api\contract\v1\UpdateCustomerPaymentProfileRequest' => $libDir . 'net/authorize/api/contract/v1/UpdateCustomerPaymentProfileRequest.php',
    'net\authorize\api\contract\v1\UpdateCustomerPaymentProfileResponse' => $libDir . 'net/authorize/api/contract/v1/UpdateCustomerPaymentProfileResponse.php',
    'net\authorize\api\contract\v1\UpdateCustomerProfileRequest' => $libDir . 'net/authorize/api/contract/v1/UpdateCustomerProfileRequest.php',
    'net\authorize\api\contract\v1\UpdateCustomerProfileResponse' => $libDir . 'net/authorize/api/contract/v1/UpdateCustomerProfileResponse.php',
    'net\authorize\api\contract\v1\UpdateCustomerShippingAddressRequest' => $libDir . 'net/authorize/api/contract/v1/UpdateCustomerShippingAddressRequest.php',
    'net\authorize\api\contract\v1\UpdateCustomerShippingAddressResponse' => $libDir . 'net/authorize/api/contract/v1/UpdateCustomerShippingAddressResponse.php',
    'net\authorize\api\contract\v1\UpdateHeldTransactionRequest'=> $libDir . 'net/authorize/api/contract/v1/UpdateHeldTransactionRequest.php',
    'net\authorize\api\contract\v1\UpdateHeldTransactionResponse'=> $libDir . 'net/authorize/api/contract/v1/UpdateHeldTransactionResponse.php',
    'net\authorize\api\contract\v1\UpdateSplitTenderGroupRequest' => $libDir . 'net/authorize/api/contract/v1/UpdateSplitTenderGroupRequest.php',
    'net\authorize\api\contract\v1\UpdateSplitTenderGroupResponse' => $libDir . 'net/authorize/api/contract/v1/UpdateSplitTenderGroupResponse.php',
    'net\authorize\api\contract\v1\UserFieldType' => $libDir . 'net/authorize/api/contract/v1/UserFieldType.php',
    'net\authorize\api\contract\v1\ValidateCustomerPaymentProfileRequest' => $libDir . 'net/authorize/api/contract/v1/ValidateCustomerPaymentProfileRequest.php',
    'net\authorize\api\contract\v1\ValidateCustomerPaymentProfileResponse' => $libDir . 'net/authorize/api/contract/v1/ValidateCustomerPaymentProfileResponse.php',
	'net\authorize\api\contract\v1\WebCheckOutDataType' => $libDir . 'net/authorize/api/contract/v1/WebCheckOutDataType.php',
    'net\authorize\api\contract\v1\KeyManagementSchemeType\DUKPTAType' => $libDir . 'net/authorize/api/contract/v1/KeyManagementSchemeType/DUKPTAType.php',
    'net\authorize\api\contract\v1\KeyManagementSchemeType\DUKPTAType\DeviceInfoAType' => $libDir . 'net/authorize/api/contract/v1/KeyManagementSchemeType/DUKPTAType/DeviceInfoAType.php',
    'net\authorize\api\contract\v1\KeyManagementSchemeType\DUKPTAType\EncryptedDataAType' => $libDir . 'net/authorize/api/contract/v1/KeyManagementSchemeType/DUKPTAType/EncryptedDataAType.php',
    'net\authorize\api\contract\v1\KeyManagementSchemeType\DUKPTAType\ModeAType' => $libDir . 'net/authorize/api/contract/v1/KeyManagementSchemeType/DUKPTAType/ModeAType.php',
    'net\authorize\api\contract\v1\MessagesType\MessageAType' => $libDir . 'net/authorize/api/contract/v1/MessagesType/MessageAType.php',
    'net\authorize\api\contract\v1\PaymentScheduleType\IntervalAType' => $libDir . 'net/authorize/api/contract/v1/PaymentScheduleType/IntervalAType.php',
    'net\authorize\api\contract\v1\TransactionRequestType\UserFieldsAType' => $libDir . 'net/authorize/api/contract/v1/TransactionRequestType/UserFieldsAType.php',
    'net\authorize\api\contract\v1\TransactionResponseType\ErrorsAType' => $libDir . 'net/authorize/api/contract/v1/TransactionResponseType/ErrorsAType.php',
    'net\authorize\api\contract\v1\TransactionResponseType\MessagesAType' => $libDir . 'net/authorize/api/contract/v1/TransactionResponseType/MessagesAType.php',
    'net\authorize\api\contract\v1\TransactionResponseType\PrePaidCardAType' => $libDir . 'net/authorize/api/contract/v1/TransactionResponseType/PrePaidCardAType.php',
    'net\authorize\api\contract\v1\TransactionResponseType\SecureAcceptanceAType' => $libDir . 'net/authorize/api/contract/v1/TransactionResponseType/SecureAcceptanceAType.php',
    'net\authorize\api\contract\v1\TransactionResponseType\SplitTenderPaymentsAType' => $libDir . 'net/authorize/api/contract/v1/TransactionResponseType/SplitTenderPaymentsAType.php',
    'net\authorize\api\contract\v1\TransactionResponseType\UserFieldsAType' => $libDir . 'net/authorize/api/contract/v1/TransactionResponseType/UserFieldsAType.php',
    'net\authorize\api\contract\v1\TransactionResponseType\ErrorsAType\ErrorAType' => $libDir . 'net/authorize/api/contract/v1/TransactionResponseType/ErrorsAType/ErrorAType.php',
    'net\authorize\api\contract\v1\TransactionResponseType\MessagesAType\MessageAType' => $libDir . 'net/authorize/api/contract/v1/TransactionResponseType/MessagesAType/MessageAType.php',
    'net\authorize\api\contract\v1\TransactionResponseType\SplitTenderPaymentsAType\SplitTenderPaymentAType' => $libDir . 'net/authorize/api/contract/v1/TransactionResponseType/SplitTenderPaymentsAType/SplitTenderPaymentAType.php',
    'net\authorize\api\contract\v1\WebCheckOutDataType\TokenAType' => $libDir . 'net/authorize/api/contract/v1/WebCheckOutDataType/TokenAType.php',
    'net\authorize\api\contract\v1\GetTransactionListForCustomerRequest' => $libDir . 'net/authorize/api/contract/v1/GetTransactionListForCustomerRequest.php',
	
    'net\authorize\api\contract\v1\GetAUJobSummaryRequest' => $libDir . 'net/authorize/api/contract/v1/getAUJobSummaryRequest.php',
    'net\authorize\api\contract\v1\GetAUJobSummaryResponse' => $libDir . 'net/authorize/api/contract/v1/GetAUJobSummaryResponse.php',
    'net\authorize\api\contract\v1\GetAUJobDetailsRequest' => $libDir . 'net/authorize/api/contract/v1/GetAUJobDetailsRequest.php',
    'net\authorize\api\contract\v1\GetAUJobDetailsResponse' => $libDir . 'net/authorize/api/contract/v1/GetAUJobDetailsResponse.php',

    'net\authorize\api\contract\v1\AuDeleteType' => $libDir . 'net/authorize/api/contract/v1/AuDeleteType.php',
    'net\authorize\api\contract\v1\AuDetailsType' => $libDir . 'net/authorize/api/contract/v1/AuDetailsType.php',
    'net\authorize\api\contract\v1\AuResponseType' => $libDir . 'net/authorize/api/contract/v1/AuResponseType.php',
    'net\authorize\api\contract\v1\AuUpdateType' => $libDir . 'net/authorize/api/contract/v1/AuUpdateType.php',

    'net\authorize\api\contract\v1\ListOfAUDetailsType' => $libDir . 'net/authorize/api/contract/v1/ListOfAUDetailsType.php',
    'net\authorize\api\contract\v1\EmvTagType' => $libDir . 'net/authorize/api/contract/v1/EmvTagType.php',
    'net\authorize\api\contract\v1\PaymentEmvType' => $libDir . 'net/authorize/api/contract/v1/PaymentEmvType.php',
    'net\authorize\api\contract\v1\OtherTaxType' => $libDir . 'net/authorize/api/contract/v1/OtherTaxType.php',
    'net\authorize\api\contract\v1\UpdateMerchantDetailsRequest' => $libDir . 'net/authorize/api/contract/v1/UpdateMerchantDetailsRequest.php',
    'net\authorize\api\contract\v1\UpdateMerchantDetailsResponse' => $libDir . 'net/authorize/api/contract/v1/UpdateMerchantDetailsResponse.php',
    'net\authorize\api\contract\v1\WebCheckOutDataTypeTokenType' => $libDir . 'net/authorize/api/contract/v1/WebCheckOutDataTypeTokenType.php',

    //Controllers
    'net\authorize\api\controller\ARBCancelSubscriptionController' => $libDir . 'net/authorize/api/controller/ARBCancelSubscriptionController.php',
    'net\authorize\api\controller\ARBCreateSubscriptionController' => $libDir . 'net/authorize/api/controller/ARBCreateSubscriptionController.php',
    'net\authorize\api\controller\ARBGetSubscriptionController' => $libDir . 'net/authorize/api/controller/ARBGetSubscriptionController.php',
    'net\authorize\api\controller\ARBGetSubscriptionListController' => $libDir . 'net/authorize/api/controller/ARBGetSubscriptionListController.php',
    'net\authorize\api\controller\ARBGetSubscriptionStatusController' => $libDir . 'net/authorize/api/controller/ARBGetSubscriptionStatusController.php',
    'net\authorize\api\controller\ARBUpdateSubscriptionController' => $libDir . 'net/authorize/api/controller/ARBUpdateSubscriptionController.php',
    'net\authorize\api\controller\AuthenticateTestController' => $libDir . 'net/authorize/api/controller/AuthenticateTestController.php',
    'net\authorize\api\controller\CreateCustomerPaymentProfileController' => $libDir . 'net/authorize/api/controller/CreateCustomerPaymentProfileController.php',
    'net\authorize\api\controller\CreateCustomerProfileController' => $libDir . 'net/authorize/api/controller/CreateCustomerProfileController.php',
    'net\authorize\api\controller\CreateCustomerProfileFromTransactionController' => $libDir . 'net/authorize/api/controller/CreateCustomerProfileFromTransactionController.php',
    'net\authorize\api\controller\CreateCustomerProfileTransactionController' => $libDir . 'net/authorize/api/controller/CreateCustomerProfileTransactionController.php',
    'net\authorize\api\controller\CreateCustomerShippingAddressController' => $libDir . 'net/authorize/api/controller/CreateCustomerShippingAddressController.php',
    'net\authorize\api\controller\CreateTransactionController' => $libDir . 'net/authorize/api/controller/CreateTransactionController.php',
    'net\authorize\api\controller\DecryptPaymentDataController' => $libDir . 'net/authorize/api/controller/DecryptPaymentDataController.php',
    'net\authorize\api\controller\DeleteCustomerPaymentProfileController' => $libDir . 'net/authorize/api/controller/DeleteCustomerPaymentProfileController.php',
    'net\authorize\api\controller\DeleteCustomerProfileController' => $libDir . 'net/authorize/api/controller/DeleteCustomerProfileController.php',
    'net\authorize\api\controller\DeleteCustomerShippingAddressController' => $libDir . 'net/authorize/api/controller/DeleteCustomerShippingAddressController.php',
    'net\authorize\api\controller\GetAUJobDetailsController' => $libDir . 'net/authorize/api/controller/GetAUJobDetailsController.php',
    'net\authorize\api\controller\GetAUJobSummaryController' => $libDir . 'net/authorize/api/controller/GetAUJobSummaryController.php',
    'net\authorize\api\controller\GetBatchStatisticsController' => $libDir . 'net/authorize/api/controller/GetBatchStatisticsController.php',
    'net\authorize\api\controller\GetCustomerPaymentProfileController' => $libDir . 'net/authorize/api/controller/GetCustomerPaymentProfileController.php',
    'net\authorize\api\controller\GetCustomerPaymentProfileListController' => $libDir . 'net/authorize/api/controller/GetCustomerPaymentProfileListController.php',
    'net\authorize\api\controller\GetCustomerProfileController' => $libDir . 'net/authorize/api/controller/GetCustomerProfileController.php',
    'net\authorize\api\controller\GetCustomerProfileIdsController' => $libDir . 'net/authorize/api/controller/GetCustomerProfileIdsController.php',
    'net\authorize\api\controller\GetCustomerShippingAddressController' => $libDir . 'net/authorize/api/controller/GetCustomerShippingAddressController.php',
    'net\authorize\api\controller\GetHostedPaymentPageController' => $libDir . 'net/authorize/api/controller/GetHostedPaymentPageController.php',
    'net\authorize\api\controller\GetHostedProfilePageController' => $libDir . 'net/authorize/api/controller/GetHostedProfilePageController.php',
    'net\authorize\api\controller\GetMerchantDetailsController' => $libDir . 'net/authorize/api/controller/GetMerchantDetailsController.php',
    'net\authorize\api\controller\GetSettledBatchListController' => $libDir . 'net/authorize/api/controller/GetSettledBatchListController.php',
    'net\authorize\api\controller\GetTransactionDetailsController' => $libDir . 'net/authorize/api/controller/GetTransactionDetailsController.php',
    'net\authorize\api\controller\GetTransactionListController' => $libDir . 'net/authorize/api/controller/GetTransactionListController.php',
    'net\authorize\api\controller\GetTransactionListForCustomerController' => $libDir . 'net/authorize/api/controller/GetTransactionListForCustomerController.php',
    'net\authorize\api\controller\GetUnsettledTransactionListController' => $libDir . 'net/authorize/api/controller/GetUnsettledTransactionListController.php',
    'net\authorize\api\controller\IsAliveController' => $libDir . 'net/authorize/api/controller/IsAliveController.php',
    'net\authorize\api\controller\LogoutController' => $libDir . 'net/authorize/api/controller/LogoutController.php',
    'net\authorize\api\controller\MobileDeviceLoginController' => $libDir . 'net/authorize/api/controller/MobileDeviceLoginController.php',
    'net\authorize\api\controller\MobileDeviceRegistrationController' => $libDir . 'net/authorize/api/controller/MobileDeviceRegistrationController.php',
    'net\authorize\api\controller\SecurePaymentContainerController' => $libDir . 'net/authorize/api/controller/SecurePaymentContainerController.php',
    'net\authorize\api\controller\SendCustomerTransactionReceiptController' => $libDir . 'net/authorize/api/controller/SendCustomerTransactionReceiptController.php',
    'net\authorize\api\controller\UpdateCustomerPaymentProfileController' => $libDir . 'net/authorize/api/controller/UpdateCustomerPaymentProfileController.php',
    'net\authorize\api\controller\UpdateCustomerProfileController' => $libDir . 'net/authorize/api/controller/UpdateCustomerProfileController.php',
    'net\authorize\api\controller\UpdateCustomerShippingAddressController' => $libDir . 'net/authorize/api/controller/UpdateCustomerShippingAddressController.php',
    'net\authorize\api\controller\UpdateHeldTransactionController' => $libDir . 'net/authorize/api/controller/UpdateHeldTransactionController.php',
    'net\authorize\api\controller\UpdateMerchantDetailsController' => $libDir . 'net/authorize/api/controller/UpdateMerchantDetailsController.php',
    'net\authorize\api\controller\UpdateSplitTenderGroupController' => $libDir . 'net/authorize/api/controller/UpdateSplitTenderGroupController.php',
    'net\authorize\api\controller\ValidateCustomerPaymentProfileController' => $libDir . 'net/authorize/api/controller/ValidateCustomerPaymentProfileController.php'

);
