import {
  Button, Box, Dialog, DialogContent, DialogTitle, TextField, Grid, InputAdornment, IconButton,
} from '@mui/material';
import React, { useEffect, useState } from 'react';
import { CustomerAddService, CustomerEditService, UnblockUserService } from '../../services/LoginPageService';
import { AddCustomerValidate } from '../../validation/formValidation';
import NotificationBar from '../notification/ServiceNotificationBar';
import previewImage from '../../images/previewImageSmall.png';
import { Visibility, VisibilityOff } from '@mui/icons-material';

function CustomerModal({
  open, setOpen, isAddButton, customerData, setRefreshData,
}) {
  const [id, setId] = useState('');
  const [customerName, setCustomerName] = useState('');
  const [email, setEmail] = useState('');
  const [systemEmail, setSystemEmail] = useState('');
  const [systemPassword,setSystemPassword] = useState('');
  const [phoneNo, setPhone] = useState('');
  const [address, setAddress] = useState('');
  const [customerId, setCustomerID] = useState('');
  const [customerLogo, setCustomerLogo] = useState('');
  const [customerImage, setCustomerImage] = useState('');
  const [previewBuilding, setPreviewBuilding] = useState('');
  const [previewBuilding2, setPreviewBuilding2] = useState('');
  const [password, setConfirmPassword] = useState('');
  const [alertLogInterval, setAlertLogInterval] = useState('');
  const [deviceLogInterval, setDeviceLogInterval] = useState('');
  const [sensorLogInterval, setSensorLogInterval] = useState('');
  const [dataRetentionPeriodInterval, setDataRetentionPeriodInterval] = useState('');
  const [expireDateReminder, setExpireDateReminder] = useState('');
  const [periodicBackupInterval, setPeriodicBackupInterval] = useState('');
  const [btnReset, setBtnReset] = useState(false);
  const [errorObject, setErrorObject] = useState({});
  const [showPassword, setShowPassword] = useState(false);

  const [openNotification, setNotification] = useState({
    status: false,
    type: 'error',
    message: '',
  });

  useEffect(() => {
    setOpen(open);
    loadData();
  }, [customerData]);

  const loadData = () => {
    setId(customerData.id || '');
    setCustomerName(customerData.customerName || '');
    setEmail(customerData.email || '');
    setPhone(customerData.phoneNo || '');
    setAddress(customerData.address || '');
    setCustomerID(customerData.customerId || '');
    setAlertLogInterval(customerData.alertLogInterval || '');
    setDeviceLogInterval(customerData.deviceLogInterval || '');
    setSensorLogInterval(customerData.sensorLogInterval || '');
    setDataRetentionPeriodInterval(customerData.dataRetentionPeriodInterval || '');
    setExpireDateReminder(customerData.expireDateReminder || '');
    setPeriodicBackupInterval(customerData.periodicBackupInterval || '');
    setPreviewBuilding(customerData.customerLogo ? `${process.env.REACT_APP_API_ENDPOINT}blog/public/${customerData.customerLogo}?${new Date().getTime()}` : previewImage);
    //setPreviewBuilding(customerData.customerLogo ? `http://localhost/backend/blog/public/${customerData.customerLogo}?${new Date().getTime()}` : previewImage);
    //setPreviewBuilding2(customerData.customerLogo ? `http://localhost/backend/blog/public/${customerData.customerImage}?${new Date().getTime()}` : previewImage);
    setPreviewBuilding2(customerData.customerLogo ? `${process.env.REACT_APP_API_ENDPOINT}blog/public/${customerData.customerImage}?${new Date().getTime()}` : previewImage);
    setCustomerLogo('');
  };
  const validateForNullValue = (value, type) => {
    AddCustomerValidate(value, type, setErrorObject);
  };

  const handleSuccess = (dataObject) => {
    setRefreshData((oldvalue) => !oldvalue);
    setNotification({
      status: true,
      type: 'success',
      message: dataObject.message,
    });
    setTimeout(() => {
      handleClose();
      setOpen(false);
    }, 5000);
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (isAddButton) {
      CustomerAddService({
        customerName, email,systemEmail,systemPassword, phoneNo, address, customerId, customerLogo, alertLogInterval, deviceLogInterval, sensorLogInterval, dataRetentionPeriodInterval, expireDateReminder, periodicBackupInterval,customerImage,
      }, handleSuccess, handleException);
    } else {
      CustomerEditService({
        id, customerName, email,systemEmail,systemPassword, phoneNo, address, customerId, customerLogo, alertLogInterval, deviceLogInterval, sensorLogInterval, dataRetentionPeriodInterval, expireDateReminder, periodicBackupInterval,customerImage
      }, handleSuccess, handleException);
    }
  };

  const handleException = (errorObject, errorMessage) => {
    setNotification({
      status: true,
      type: 'error',
      message: errorMessage,
    });
    setTimeout(() => {
      handleClose();
    }, 5000);
  };

  const passwordSubmit = (e) => {
    e.preventDefault();
    UnblockUserService({ email, password, id }, passwordValidationSuccess, passwordValidationException);
    setBtnReset(false);
  };
  const passwordValidationSuccess = (dataObject) => {
    setNotification({
      status: true,
      type: 'success',
      message: dataObject.message,
    });
    setTimeout(() => {
      handleClose();
    }, 5000);
  };

  const passwordValidationException = ( errorObject, errorMessage) => {
    setNotification({
      status: true,
      type: 'error',
      message: errorMessage,
    });
    setTimeout(() => {
      handleClose();
    }, 5000);
  };

  const handleClose = () => {
    setNotification({
      status: false,
      type: '',
      message: '',
    });
  };

  const handleClickShowPassword = () => {
    setShowPassword(!showPassword);
  };

  const handleMouseDownPassword = (event) => {
    event.preventDefault();
  };

  return (
    <Dialog
      sx={{ '& .MuiDialog-paper': { width: '72%', maxHeight: '100%' } }}
      maxWidth="lg"
      open={open}
    >
      <DialogTitle sx={{fontFamily:'customfont', fontWeight:'600', letterSpacing:'1px'}}>
        {isAddButton ? 'Add Customer' : 'Edit Customer'}
      </DialogTitle>
      <DialogContent>
        <form className="mt-2 space-y-6" onSubmit={handleSubmit}>
          <div className="rounded-md  -space-y-px px-10 ">
            <Grid container spacing={2}>
              <Grid item xs={6}>
                <div className="rounded-md -space-y-px mb-2">
                  <TextField
                    sx={{ mb: 1 }}
                    label="Customer Name"
                    type="text"
                    value={customerName}
                    variant="outlined"
                    // placeholder="Customer Name"
                    /* eslint-disable-next-line */
                    className="mb-2 appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-red-500 focus:border-red-500  sm:text-sm"
                    required
                    onBlur={() => validateForNullValue(customerName, 'fullName')}
                    onChange={(e) => { setCustomerName(e.target.value); }}
                    autoComplete="off"
                    error={errorObject?.fullName?.errorStatus}
                    helperText={errorObject?.fullName?.helperText}
                    InputLabelProps={{
                      shrink:'true',
                      style:{fontFamily:'customfont'}
                    }}
                    InputProps={{
                      style:{fontFamily:'customfont'}
                    }}
                  />
                </div>
              </Grid>
              <Grid item xs={6}>
                <div className="rounded-md -space-y-px">
                  <div className="mb-2">
                    <TextField
                      sx={{ mb: 1 }}
                      label="Customer Email Id"
                      type="email"
                      value={email}
                      variant="outlined"
                      // placeholder="Customer Email Id"
                      /* eslint-disable-next-line */
                      className="mb-2 appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-red-500 focus:border-red-500  sm:text-sm"
                      required
                      onBlur={() => { validateForNullValue(email, 'email'); }}
                      onChange={(e) => { setEmail(e.target.value); }}
                      autoComplete="off"
                      error={errorObject?.emailID?.errorStatus}
                      helperText={errorObject?.emailID?.helperText}
                      InputLabelProps={{
                        shrink:'true',
                        style:{fontFamily:'customfont'}
                      }}
                      InputProps={{
                        style:{fontFamily:'customfont'}
                      }}
                    />
                  </div>
                </div>
              </Grid>

             
                <Grid item  xs={12}>
                  <label style={{fontFamily:'customfont', fontWeight:'600'}}>System Generated Communication</label>
                </Grid>

                <Grid item xs={6}>
                <div className="rounded-md -space-y-px">
                  <div className="mb-2">
                    <TextField
                      sx={{ mb: 1 }}
                      label="System Email Id"
                      type="System Email Id"
                      value={systemEmail}
                      variant="outlined"
                      // placeholder="System Email Id"
                      /* eslint-disable-next-line */
                      className="mb-2 appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-red-500 focus:border-red-500  sm:text-sm"
                      required
                      onBlur={() => { validateForNullValue(systemEmail, 'systemEmail'); }}
                      onChange={(e) => { setSystemEmail(e.target.value); }}
                      autoComplete="off"
                      error={errorObject?.systemEmail?.errorStatus}
                      helperText={errorObject?.systemEmail?.helperText}
                      InputLabelProps={{
                        shrink:'true',
                        style:{fontFamily:'customfont'}
                      }}
                      InputProps={{
                        style:{fontFamily:'customfont'}
                      }}
                    />
                  </div>
                </div>
              </Grid>

              <Grid item xs={6}>
                <div className="rounded-md -space-y-px">
                  <div className="mb-2">
                  <TextField
                    fullWidth
                    sx={{ mb: 1 }}
                    label="Password"
                    type={showPassword ? 'text' : 'password'}
                    value={systemPassword}
                    variant="outlined"
                    // placeholder="Password"
                    required
                    onChange={(e) => setSystemPassword(e.target.value)}
                    onBlur={() => { validateForNullValue(systemPassword, 'systemPassword'); }}
                    autoComplete="off"
                    error={errorObject?.systemPassword?.errorStatus}
                    helperText={errorObject?.systemPassword?.helperText}
                    InputLabelProps={{
                      shrink:'true',
                      style:{fontFamily:'customfont'}
                    }}
                    InputProps={{
                      style:{fontFamily:'customfont'},
                    endAdornment: (
                      <InputAdornment  position="end">
                        <IconButton
                          onClick={handleClickShowPassword}
                          onMouseDown={handleMouseDownPassword}
                          edge="end"
                  >
              {showPassword ? <VisibilityOff /> : <Visibility />}
            </IconButton>
          </InputAdornment>
        ),
      }}
    />
                  </div>
                </div>
              </Grid>

            
              <Grid item xs={6}>
                <div className="rounded-md -space-y-px">
                  <div className="mb-2">
                    <TextField
                      sx={{ mb: 1 }}
                      label="Phone number"
                      type="number"
                      value={phoneNo}
                      variant="outlined"
                      // placeholder="Phone number"
                      /* eslint-disable-next-line */
                      className="mb-2 appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-red-500 focus:border-red-500  sm:text-sm"
                      required
                      onBlur={() => validateForNullValue(phoneNo, 'phone')}
                      onChange={(e) => { setPhone(e.target.value); }}
                      autoComplete="off"
                      error={errorObject?.phone?.errorStatus}
                      helperText={errorObject?.phone?.helperText}
                      InputLabelProps={{
                        shrink:'true',
                        style:{fontFamily:'customfont'}
                      }}
                      InputProps={{
                        style:{fontFamily:'customfont'}
                      }}
                    />
                  </div>
                </div>
              </Grid>
              <Grid item xs={6}>
                <div className="rounded-md -space-y-px">
                  <div className="mb-2">
                    <TextField
                      sx={{ mb: 1 }}
                      label="Address"
                      type="text"
                      value={address}
                      variant="outlined"
                      // placeholder="Address"
                      /* eslint-disable-next-line */
                      className="mb-2 appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-red-500 focus:border-red-500  sm:text-sm"
                      required
                      onBlur={() => validateForNullValue(address, 'address')}
                      onChange={(e) => setAddress(e.target.value)}
                      autoComplete="off"
                      error={errorObject?.address?.errorStatus}
                      helperText={errorObject?.address?.helperText}
                      InputLabelProps={{
                        shrink:'true',
                        style:{fontFamily:'customfont'}
                      }}
                      InputProps={{
                        style:{fontFamily:'customfont'}
                      }}
                    />
                  </div>
                </div>
              </Grid>
              <Grid item xs={6}>
                <div className="rounded-md -space-y-px">
                  <div className="mb-2">
                    <TextField
                      sx={{ mb: 1 }}
                      label="Customer Id"
                      type="text"
                      value={customerId}
                      variant="outlined"
                      // placeholder="Customer Id"
                      disabled={isAddButton ? false : true}
                      /* eslint-disable-next-line */
                      className="mb-2 appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-red-500 focus:border-red-500  sm:text-sm"
                      required
                      onBlur={() => validateForNullValue(customerId, 'customerID')}
                      onChange={(e) => setCustomerID(e.target.value)}
                      autoComplete="off"
                      error={errorObject?.customerID?.errorStatus}
                      helperText={errorObject?.customerID?.helperText}
                      InputLabelProps={{
                        shrink:'true',
                        style:{fontFamily:'customfont'}
                      }}
                      InputProps={{
                        style:{fontFamily:'customfont'}
                      }}
                    />
                  </div>
                </div>
              </Grid>
              <Grid item xs={5} spacing={2} style={{display:'flex',justifyContent: 'space-between'}}>
                <div className="col-span-12 sm:col-span-8 lg:col-span-8">
                  <div className="mb-2 block">
                    <TextField
                      fullWidth
                      label="Customer Image"
                      onBlur={() => {
                        validateForNullValue(customerImage, 'customerImage');
                      }}
                      onChange={(e) => {
                        if (e.target.files && e.target.files.length > 0) {
                          const reader = new FileReader();
                          reader.onload = () => {
                            if (reader.readyState === 2) {
                              setCustomerImage(reader.result);
                              setPreviewBuilding2(reader.result);
                            }
                          };
                          reader.readAsDataURL(e.target.files[0]);
                        }
                      }}
                      InputLabelProps={{ shrink: true }}
                      type="file"
                      inputProps={{
                        accept: 'image/png',
                      }}
                      error={errorObject?.customerImage?.errorStatus}
                      helperText={errorObject?.customerImage?.helperText}
                    />
                  </div>
                </div>
             
                <div className="col-span-12 sm:col-span-2 lg:col-span-2">
                  <div className="mb-2 block">
                    <Box
                      component="img"
                      sx={{
                        height: 100,
                        width: 100,
                        maxHeight: { xs: 233, md: 167 },
                        maxWidth: { xs: 150, md: 150 },
                      }}
                      alt="The Customer Buidling Image"
                      src={previewBuilding2 || previewImage}
                    />
                  </div>
                </div>
              </Grid>
              <Grid item xs={6}>
                <div className="rounded-md -space-y-px">
                  <div className="mb-2">
                    <TextField
                      sx={{ mb: 1 }}
                      label="Alert Log Interval(Seconds)"
                      type="number"
                      value={alertLogInterval}
                      variant="outlined"
                      // placeholder="Alert Log Interval(Seconds)"
                      /* eslint-disable-next-line */
                      className="mb-2 appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-red-500 focus:border-red-500  sm:text-sm"
                      required
                      onBlur={() => validateForNullValue(alertLogInterval, 'alertLogInterval')}
                      onChange={(e) => setAlertLogInterval(e.target.value)}
                      autoComplete="off"
                      error={errorObject?.alertLogInterval?.errorStatus}
                      helperText={errorObject?.alertLogInterval?.helperText}
                      InputLabelProps={{
                        shrink:'true',
                        style:{fontFamily:'customfont'}
                      }}
                      InputProps={{
                        style:{fontFamily:'customfont'}
                      }}
                    />
                  </div>
                </div>
              </Grid>
              <Grid item xs={6}>
                <div className="rounded-md -space-y-px">
                  <div className="mb-2">
                    <TextField
                      sx={{ mb: 1 }}
                      label="Device Log Interval(Seconds)"
                      type="number"
                      value={deviceLogInterval}
                      variant="outlined"
                      // placeholder="Device Log Interval(Seconds)"
                      /* eslint-disable-next-line */
                      className="mb-2 appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-red-500 focus:border-red-500  sm:text-sm"
                      required
                      onBlur={() => validateForNullValue(deviceLogInterval, 'deviceLogInterval')}
                      onChange={(e) => setDeviceLogInterval(e.target.value)}
                      autoComplete="off"
                      error={errorObject?.deviceLogInterval?.errorStatus}
                      helperText={errorObject?.deviceLogInterval?.helperText}
                      InputLabelProps={{
                        shrink:'true',
                        style:{fontFamily:'customfont'}
                      }}
                      InputProps={{
                        style:{fontFamily:'customfont'}
                      }}
                    />
                  </div>
                </div>
              </Grid>
              <Grid item xs={6}>
                <div className="rounded-md -space-y-px">
                  <div className="mb-2">
                    <TextField
                      sx={{ mb: 1 }}
                      label="Sensor Log Interval(Seconds)"
                      type="number"
                      value={sensorLogInterval}
                      variant="outlined"
                      // placeholder="Sensor Log Interval(Seconds)"
                      /* eslint-disable-next-line */
                      className="mb-2 appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-red-500 focus:border-red-500  sm:text-sm"
                      required
                      onBlur={() => validateForNullValue(sensorLogInterval, 'sensorLogInterval')}
                      onChange={(e) => setSensorLogInterval(e.target.value)}
                      autoComplete="off"
                      error={errorObject?.sensorLogInterval?.errorStatus}
                      helperText={errorObject?.sensorLogInterval?.helperText}
                      InputLabelProps={{
                        shrink:'true',
                        style:{fontFamily:'customfont'}
                      }}
                      InputProps={{
                        style:{fontFamily:'customfont'}
                      }}
                    />
                  </div>
                </div>
              </Grid>
              <Grid item xs={6}>
                <div className="rounded-md -space-y-px">
                  <div className="mb-2">
                    <TextField
                      sx={{ mb: 1 }}
                      label="Data Retention Period(Days)"
                      type="number"
                      value={dataRetentionPeriodInterval}
                      variant="outlined"
                      // placeholder="Data Retention Period(Days)"
                      /* eslint-disable-next-line */
                      className="mb-2 appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-red-500 focus:border-red-500  sm:text-sm"
                      required
                      onBlur={() => validateForNullValue(dataRetentionPeriodInterval, 'dataRetentionPeriodInterval')}
                      onChange={(e) => setDataRetentionPeriodInterval(e.target.value)}
                      autoComplete="off"
                      error={errorObject?.dataRetentionPeriodInterval?.errorStatus}
                      helperText={errorObject?.dataRetentionPeriodInterval?.helperText}
                      InputLabelProps={{
                        shrink:'true',
                        style:{fontFamily:'customfont'}
                      }}
                      InputProps={{
                        style:{fontFamily:'customfont'}
                      }}
                    />
                  </div>
                </div>
              </Grid>
              <Grid item xs={6}>
                <div className="rounded-md -space-y-px">
                  <div className="mb-2">
                    <TextField
                      sx={{ mb: 1 }}
                      label="Expire Date Reminder(Days)"
                      type="number"
                      value={expireDateReminder}
                      variant="outlined"
                      // placeholder="Expire Date Reminder(Days)"
                      /* eslint-disable-next-line */
                      className="mb-2 appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-red-500 focus:border-red-500  sm:text-sm"
                      required
                      onBlur={() => validateForNullValue(expireDateReminder, 'expireDateReminder')}
                      onChange={(e) => setExpireDateReminder(e.target.value)}
                      autoComplete="off"
                      error={errorObject?.expireDateReminder?.errorStatus}
                      helperText={errorObject?.expireDateReminder?.helperText}
                      InputLabelProps={{
                        shrink:'true',
                        style:{fontFamily:'customfont'}
                      }}
                      InputProps={{
                        style:{fontFamily:'customfont'}
                      }}
                    />
                  </div>
                </div>
              </Grid>
              <Grid item xs={6}>
                <div className="rounded-md -space-y-px">
                  <div className="mb-2">
                    <TextField
                      sx={{ mb: 1 }}
                      label="Periodic Backup Interval(Days)"
                      type="number"
                      value={periodicBackupInterval}
                      variant="outlined"
                      // placeholder="Periodic Backup Interval(Days)"
                      /* eslint-disable-next-line */
                      className="mb-2 appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-red-500 focus:border-red-500  sm:text-sm"
                      required
                      onBlur={() => validateForNullValue(periodicBackupInterval, 'periodicBackupInterval')}
                      onChange={(e) => setPeriodicBackupInterval(e.target.value)}
                      autoComplete="off"
                      error={errorObject?.periodicBackupInterval?.errorStatus}
                      helperText={errorObject?.periodicBackupInterval?.helperText}
                      InputLabelProps={{
                        shrink:'true',
                        style:{fontFamily:'customfont'}
                      }}
                      InputProps={{
                        style:{fontFamily:'customfont'}
                      }}
                    />
                  </div>
                </div>
              </Grid>
              <Grid item xs={9}>
                <div className="col-span-12 sm:col-span-8 lg:col-span-8">
                  <div className="mb-2 block">
                    <TextField
                      fullWidth
                      label="Company Logo"
                      onBlur={() => {
                        validateForNullValue(customerLogo, 'customerLogo');
                      }}
                      onChange={(e) => {
                        if (e.target.files && e.target.files.length > 0) {
                          const reader = new FileReader();
                          reader.onload = () => {
                            if (reader.readyState === 2) {
                              setCustomerLogo(reader.result);
                              setPreviewBuilding(reader.result);
                            }
                          };
                          reader.readAsDataURL(e.target.files[0]);
                        }
                      }}
                      InputLabelProps={{ shrink: true }}
                      type="file"
                      inputProps={{
                        accept: 'image/png',
                      }}
                      error={errorObject?.customerLogo?.errorStatus}
                      helperText={errorObject?.customerLogo?.helperText}
                    />
                  </div>
                </div>
              </Grid>
              <Grid item xs={3}>
                <div className="col-span-12 sm:col-span-2 lg:col-span-2">
                  <div className="mb-2 block">
                    <Box
                      component="img"
                      sx={{
                        height: 100,
                        width: 100,
                        maxHeight: { xs: 233, md: 167 },
                        maxWidth: { xs: 150, md: 150 },
                      }}
                      alt="The Customer Buidling Image"
                      src={previewBuilding || previewImage}
                    />
                  </div>
                </div>
              </Grid>
            </Grid>
            <div className="rounded-md -space-y-px float-right">
              {isAddButton ? ''
                : (
                  <Button
                  sx={{
                    height: '0',
                    // width:'100%',
                    padding: "10px 19px",
                    color: 'white',
                    marginTop: '10px',
                    marginBottom: '15px',
                    fontSize: '13px',
                    borderRadius: '10px',
                    fontWeight: '600',
                    fontFamily: 'customfont',
                    letterSpacing: '1px',
                    marginRight:'20px'
                  }}
                  style={{
                    background: 'rgb(19, 60, 129)',}}
                    onClick={() => {
                      setBtnReset(true);
                    }}
                  >
                    Reset Password
                  </Button>
                )}
              <Button
                sx={{
                  height: '0',
                  // width:'100%',
                  padding: "10px 19px",
                  color: 'white',
                  marginTop: '20px',
                  marginBottom: '15px',
                  fontSize: '13px',
                  borderRadius: '10px',
                  fontWeight: '600',
                  fontFamily: 'customfont',
                  letterSpacing: '1px',
                  marginRight:'20px'
                }}
                style={{
                  background: 'rgb(19, 60, 129)',}}
                type="submit"
                /* eslint-disable-next-line */
                // disabled={errorObject?.fullName?.errorStatus || errorObject?.emailID?.errorStatus || errorObject?.phone?.errorStatus || errorObject?.address?.errorStatus || errorObject?.customerID?.errorStatus || errorObject?.customerTheme?.errorStatus || errorObject?.customerLogo?.errorStatus}
              >
                {isAddButton ? 'Add' : 'Update'}
              </Button>
              <Button
                sx={{
                  height: '0',
                  // width:'100%',
                  padding: "10px 19px",
                  color: 'white',
                  marginTop: '20px',
                  marginBottom: '15px',
                  fontSize: '13px',
                  borderRadius: '10px',
                  fontWeight: '600',
                  fontFamily: 'customfont',
                  letterSpacing: '1px',
                  marginRight:'20px'
                }}
                style={{
                  background: 'rgb(19, 60, 129)',}}
              /* eslint-disable-next-line */
                onClick={(e) => {
                  setOpen(false);
                  setErrorObject({});
                  loadData();
                }}
              >
                Cancel
              </Button>
            </div>
          </div>
        </form>
      </DialogContent>
      <Dialog
        maxWidth="sm"
        open={btnReset}
      >
        <DialogTitle>
          Confirm your password
        </DialogTitle>
        <DialogContent>
          <form onSubmit={passwordSubmit}>
            <div className="col-span-6 sm:col-span-2 lg:col-span-2 ">
              <div className="inline">
                <TextField
                  placeholder="Enter your password"
                  type="password"
                  required
                  onChange={(e) => {
                    setConfirmPassword(e.target.value);
                  }}
                />
              </div>
            </div>
            <div className="mt-3 ml-2 float-right">
              <Button
                sx={{
                  height: '0',
                  width:'100%',
                  padding: "10px 19px",
                  color: 'white',
                  marginTop: '20px',
                  marginBottom: '15px',
                  fontSize: '13px',
                  borderRadius: '10px',
                  fontWeight: '600',
                  fontFamily: 'customfont',
                  letterSpacing: '1px'
                }}
                style={{
                  background: 'rgb(19, 60, 129)',}}
                onClick={() => {
                  setBtnReset(false);
                }}
              >
                Cancel
              </Button>
              <Button
                sx={{
                  height: '0',
                  width:'100%',
                  padding: "10px 19px",
                  color: 'white',
                  marginTop: '20px',
                  marginBottom: '15px',
                  fontSize: '13px',
                  borderRadius: '10px',
                  fontWeight: '600',
                  fontFamily: 'customfont',
                  letterSpacing: '1px'
                }}
                style={{
                  background: 'rgb(19, 60, 129)',}}
                type="submit"
              >
                Submit
              </Button>
            </div>
          </form>
        </DialogContent>
      </Dialog>
      <NotificationBar
        handleClose={handleClose}
        notificationContent={openNotification.message}
        openNotification={openNotification.status}
        type={openNotification.type}
      />
    </Dialog>
  );
}

export default CustomerModal;
