import React, { useState, useEffect } from 'react';
import {
  Button,
  DialogContent,
  InputLabel,
  Select,
  Typography,
  FormControl,
  TextField,
  MenuItem,
  Grid,
  Box,
  RadioGroup,
  FormControlLabel,
  Radio,
} from '@mui/material';

import {
  CategoryFetchService,
  DynamicUnitListService,
  SensorAddService,
  SensorCategoryFetchService,
  SensorEditService,
} from '../../services/LoginPageService';
import Analog from './sensorType/AnalogComponent';
import Modbus from './sensorType/ModbusComponent';
import NotificationBar from '../notification/ServiceNotificationBar';
import { AddCategoryValidate } from '../../validation/formValidation';
import StelTWA from './sensorType/StelTWAComponent';
import SensorAlertRange from './categoryManagement/addSensors/subComponent/SensorAlertRangeComponent';
import ApplicationStore from '../../utils/localStorageUtil';
import { OutlinedInput } from '@mui/material';

function SensorConfig({
  locationDetails, setOpen, editData, isAddButton, setRefreshData,
}) {
  const [nextPage, setNextPage] = useState(true);
  const [nextButton, setNextButton] = useState('Next');
  const id = editData.id || '';
  const [sensorCategoryId, setSensorCategoryId] = useState(editData.sensorCategoryId || '');
  const [sensorName, setSensorName] = useState(editData.sensorName || '');
  const [manufacturer, setManufacturer] = useState(editData.manufacturer || '');
  const [partId, setPartId] = useState(editData.partId || '');
  const [sensorOutput, setSensorOutput] = useState(editData.sensorOutput || 'Digital');
  // -----analog----//
  const [sensorType, setSensorType] = useState(editData.sensorType || '');
  const [units, setUnits] = useState(editData.units || '');
  const [unitsList, setUnitsList] = useState([]);
  // const [relayOutput, setRelayOutput] = useState(editData?.relayOutput || 'ON');
  const [bumpTestRequired, setBumpTestRequired] = useState(editData?.bumpTestRequired || 'Off');
  const [minRatedReading, setMinRatedReading] = useState(editData.minRatedReading || '');
  const [minRatedReadingChecked, setMinRatedReadingChecked] = useState(editData.minRatedReadingChecked || '0');
  const [minRatedReadingScale, setMinRatedReadingScale] = useState(editData.minRatedReadingScale || '');
  const [maxRatedReading, setMaxRatedReading] = useState(editData.maxRatedReading || '');
  const [maxRatedReadingChecked, setMaxRatedReadingChecked] = useState(editData.maxRatedReadingChecked || '0');
  const [maxRatedReadingScale, setMaxRatedReadingScale] = useState(editData.maxRatedReadingScale || '');
  // -Modbus--------//
  const [slaveId, setSlaveId] = useState(editData.slaveId || '');
  const [registerId, setRegisterId] = useState(editData.registerId || '');
  const [length, setLength] = useState(editData.length || '');
  const [registerType, setRegisterType] = useState(editData.registerType || '');
  const [conversionType, setConversionType] = useState(editData.conversionType || '');
  const [ipAddress, setIpAddress] = useState(editData.ipAddress || '');
  const [subnetMask, setSubnetMask] = useState(editData.subnetMask || '');

  const [sensorCategoryList, setSensorCategoryList] = useState([]);

  // --- Alarm --- //
  const [alarm, setAlarm] = useState(editData.alarm || 'Latch');
  const [isStel, setIsStel] = useState(editData ? editData.isStel === '1' : false);
  const [isAQI, setIsAQI] = useState(editData ? editData.isAQI === '1' : false);
  const [stelDuration, setStelDuration] = useState(editData.stelDuration || '');
  const [stelType, setStelType] = useState(editData.stelType || 'ppm');
  const [stelLimit, setStelLimit] = useState(editData.stelLimit || 0);
  const [stelAlert, setStelAlert] = useState(editData.stelAlert || '');
  const [twaDuration, setTwaDuration] = useState(editData.twaDuration || '');
  const [twaType, setTwaType] = useState(editData.twaType || 'ppm');
  const [twaStartTime, setTwaStartTime] = useState(editData.twaStartTime || '01:05');
  const [stelStartTime, setStelStartTime] = useState(editData.stelStartTime || '01:05');
  const [twaLimit, setTwaLimit] = useState(editData.twaLimit || 0);
  const [twaAlert, setTwaAlert] = useState(editData.twaAlert || '');

  const [parmGoodMinScale, setParmGoodMinScale] = useState(editData.parmGoodMinScale || '');
  const [parmGoodMaxScale, setParmGoodMaxScale] = useState(editData.parmGoodMaxScale || '');
  const [parmSatisfactoryMinScale, setParmSatisfactoryMinScale] = useState(editData.parmSatisfactoryMinScale || '');
  const [parmSatisfactoryMaxScale, setParmSatisfactoryMaxScale] = useState(editData.parmSatisfactoryMaxScale || '');
  const [parmModerateMinScale, setParmModerateMinScale] = useState(editData.parmModerateMinScale || '');
  const [parmModerateMaxScale, setParmModerateMaxScale] = useState(editData.parmModerateMaxScale || '');
  const [parmPoorMinScale, setParmPoorMinScale] = useState(editData.parmPoorMinScale || '');
  const [parmPoorMaxScale, setParmPoorMaxScale] = useState(editData.parmPoorMaxScale || '');
  const [parmVeryPoorMinScale, setParmVeryPoorMinScale] = useState(editData.parmVeryPoorMinScale || '');
  const [parmVeryPoorMaxScale, setParmVeryPoorMaxScale] = useState(editData.parmVeryPoorMaxScale || '');
  const [parmSevereMinScale, setParmSevereMinScale] = useState(editData.parmSevereMinScale || '');
  const [parmSevereMaxScale, setParmSevereMaxScale] = useState(editData.parmSevereMaxScale || '');

  // --- Critical Alert --- //
  const [criticalMinValue, setCriticalMinValue] = useState(editData?.criticalMinValue || '');
  const [criticalMaxValue, setCriticalMaxValue] = useState(editData?.criticalMaxValue || '');
  const [criticalRefMinValue, setRefCriticalMinValue] = useState(editData?.criticalMinValue || '');
  const [criticalRefMaxValue, setRefCriticalMaxValue] = useState(editData?.criticalMaxValue || '');

  // --- Warning Alert --- //
  const [warningMinValue, setWarningMinValue] = useState(editData?.warningMinValue || '');
  const [warningMaxValue, setWarningMaxValue] = useState(editData?.warningMaxValue || '');
  const [warningRefMinValue, setRefWarningMinValue] = useState(editData?.warningMinValue || '');
  const [warningRefMaxValue, setRefWarningMaxValue] = useState(editData?.warningMinValue || '');

  // --- Out-of-Range Alert --- //
  const [outofrangeMinValue, setOutofrangeMinValue] = useState(editData?.outofrangeMinValue || '');
  const [outofrangeMaxValue, setOutofrangeMaxValue] = useState(editData?.outofrangeMaxValue || '');
  const [outofrangeRefMinValue, setRefOutofrangeMinValue] = useState(editData?.outofrangeMinValue || '');
  const [outofrangeRefMaxValue, setRefOutofrangeMaxValue] = useState(editData?.outofrangeMaxValue || '');

  //-------Zrocheck and 
  const { userDetails } = ApplicationStore().getStorage('userDetails');
   
  const [typeCheck, setTypeCheck] = useState('zeroCheck');
  const [zeroCheckValue, setZeroCheckValue] = useState(editData?.zeroCheckValue || '');
  const [spanCheckValue,setSpanCheckValue] = useState(editData?.spanCheckValue ||'');

  const [openNotification, setNotification] = useState({
    status: false,
    type: 'error',
    message: '',
  });

  const [errorObject, setErrorObject] = useState({});

  const validateForNullValue = (value, type) => {
    AddCategoryValidate(value, type, setErrorObject);
   
  };

  /* eslint-disable-next-line */
  const handleException = (errorObject) => {
  };

  useEffect(() => {
    loadData();
  }, []);

  const categoryHandleSuccess = () => {
    // setCategoryList(dataObject.data);
  };

  const sensorCategoryHandleSuccess = (dataObject) => {
    setSensorCategoryList(dataObject.data);
  };

  const loadData = () => {
    CategoryFetchService(categoryHandleSuccess, handleException);
    SensorCategoryFetchService(sensorCategoryHandleSuccess, handleException);
    editData.sensorCategoryId && DynamicUnitListService(editData.sensorCategoryId, handleSensorUnitSuccess, handleSensorUnitException);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    /* eslint-disable-next-line */
    {
      isAddButton
        ? SensorAddService({
          ...locationDetails,
          sensorCategoryId,
          sensorName,
          manufacturer,
          partId,
          sensorOutput,
          sensorType,
          units,
          // relayOutput,
          bumpTestRequired,
          minRatedReading,
          minRatedReadingChecked,
          minRatedReadingScale,
          maxRatedReading,
          maxRatedReadingChecked,
          maxRatedReadingScale,
          slaveId,
          registerId,
          length,
          registerType,
          conversionType,
          ipAddress,
          subnetMask,
          alarm,
          isStel,
          isAQI,
          stelDuration,
          stelType,
          stelLimit,
          stelAlert,
          twaDuration,
          twaType,
          twaStartTime,
          stelStartTime,
          twaLimit,
          twaAlert,
          parmGoodMinScale,
          parmGoodMaxScale,
          parmSatisfactoryMinScale,
          parmSatisfactoryMaxScale,
          parmModerateMinScale,
          parmModerateMaxScale,
          parmPoorMinScale,
          parmPoorMaxScale,
          parmVeryPoorMinScale,
          parmVeryPoorMaxScale,
          parmSevereMinScale,
          parmSevereMaxScale,
          criticalMinValue,
          criticalMaxValue,
          warningMinValue,
          warningMaxValue,
          outofrangeMinValue,
          outofrangeMaxValue,
          typeCheck,
          zeroCheckValue,
          spanCheckValue,

        }, sensorAddSuccess, senserAddException)
        : SensorEditService({
          ...locationDetails,
          id,
          sensorCategoryId,
          sensorName,
          manufacturer,
          partId,
          sensorOutput,
          sensorType,
          units,
          // relayOutput,
          bumpTestRequired,
          minRatedReading,
          minRatedReadingChecked,
          minRatedReadingScale,
          maxRatedReading,
          maxRatedReadingChecked,
          maxRatedReadingScale,
          slaveId,
          registerId,
          length,
          registerType,
          conversionType,
          ipAddress,
          subnetMask,
          alarm,
          isStel,
          isAQI,
          stelDuration,
          stelType,
          stelLimit,
          stelAlert,
          twaDuration,
          twaType,
          twaStartTime,
          stelStartTime,
          twaLimit,
          twaAlert,
          parmGoodMinScale,
          parmGoodMaxScale,
          parmSatisfactoryMinScale,
          parmSatisfactoryMaxScale,
          parmModerateMinScale,
          parmModerateMaxScale,
          parmPoorMinScale,
          parmPoorMaxScale,
          parmVeryPoorMinScale,
          parmVeryPoorMaxScale,
          parmSevereMinScale,
          parmSevereMaxScale,
          criticalMinValue,
          criticalMaxValue,
          warningMinValue,
          warningMaxValue,
          outofrangeMinValue,
          outofrangeMaxValue,
          typeCheck,
          zeroCheckValue,
          spanCheckValue
        }, sensorAddSuccess, senserAddException);
    }
  };

  const sensorAddSuccess = (dataObject) => {
    setNotification({
      status: true,
      type: 'success',
      message: dataObject.message,
    });
    setRefreshData((oldvalue) => {
      return !oldvalue;
    });
    setTimeout(() => {
      handleClose();
      setOpen(false);
    }, 5000);
  };
  /* eslint-disable-next-line */
  const senserAddException = (errorObject, errorMessage) => {
    setNotification({
      status: true,
      type: 'error',
      message: errorMessage,
    });
  };

  const handleClose = () => {
    setNotification({
      status: false,
      type: '',
      message: '',
    });
  };

  const onSensorCategoryChange = (e) => {
    setSensorCategoryId(e.target.value);
    DynamicUnitListService(e.target.value, handleSensorUnitSuccess, handleSensorUnitException);
  };

  const handleSensorUnitSuccess = (dataObject) => {
    setUnitsList(JSON.parse(dataObject.data[0].measureUnitList?.replace(/\\/g, '').replace(/(^"|"$)/g, '')) || []);
  };

  const handleSensorUnitException = (errorObject, errorMessage) => {
    setNotification({
      status: true,
      type: 'error',
      message: errorMessage,
    });
  };

  const validateNextButton = () => {
    if(sensorCategoryId === '' || sensorName === '' || manufacturer === '' || partId === ''){
      setNotification({
        status: true,
        type: 'error',
        message: 'Please fill all the fields',
      });
    } else {
      if(sensorOutput !== 'Digital' && sensorOutput !== 'Inbuilt' ){
        if(units === '' || sensorType === '' || minRatedReading === '' || maxRatedReading === '' || minRatedReadingScale === '' || maxRatedReadingScale === ''){
          setNotification({
            status: true,
            type: 'error',
            message: 'Please fill all the fields',
          });
        } 
        else{
          if(sensorOutput === 'Modbus'){
            if(slaveId === '' || registerId === '' || length === '' || registerType === '' || conversionType === ''){
              setNotification({
                status: true,
                type: 'error',
                message: 'Please fill all the fields',
              });
            } else {
              if(sensorType === 'TCP'){
                if(ipAddress === '' || subnetMask === ''){
                  setNotification({
                    status: true,
                    type: 'error',
                    message: 'Please fill all the fields',
                  });
                } else {
                  setNextPage((oldValue) => {
                    /* eslint-disable-next-line */
                    oldValue === true ? setNextButton('Back') : setNextButton('Next');
                    return !oldValue;
                  });
                }
              } else {
                setNextPage((oldValue) => {
                  /* eslint-disable-next-line */
                  oldValue === true ? setNextButton('Back') : setNextButton('Next');
                  return !oldValue;
                });
              }
            }
          } else {
            setNextPage((oldValue) => {
              /* eslint-disable-next-line */
              oldValue === true ? setNextButton('Back') : setNextButton('Next');
              return !oldValue;
            });
          }
        }
      } else {
        setNextPage((oldValue) => {
          /* eslint-disable-next-line */
          oldValue === true ? setNextButton('Back') : setNextButton('Next');
          return !oldValue;
        });
      }
    }
  }

  const OnchangePercenttageDevi=(e)=>{
    setPercentDeviation(e.target.value);
  }

  return (
    <div className="w-full" style={{ marginTop: 0 }}>
      <form className="mt-0 px-0 sm:px-5 w-full" onSubmit={handleSubmit}>
        <DialogContent sx={{ px: 0, p: 5 }}>
          <Typography variant="h5"
          sx={{ m: 0, textAlign: 'center', fontFamily: 'customfont', fontWeight: '600', marginTop: '0px', marginBottom: '25px' }}>
            {isAddButton ? 'Add Sensor' : 'Edit Sensor'}
          </Typography>
          {nextPage === true
            ? (
              <>
                <Grid container spacing={2} sx={{ mt: 0 }}>
                  <Grid
                    sx={{ mt: 0, padding: 0 }}
                    item
                    xs={12}
                    sm={6}
                    md={6}
                    lg={6}
                    xl={6}
                  >
                    <Box>
                      <FormControl fullWidth margin="normal" sx={{ marginTop: 0 }}>
                        <InputLabel shrink id="demo-simple-select-label">
                          Sensor Category
                        </InputLabel>
                        <Select
                         input={<OutlinedInput notched label={'Sensor Category'} />}
                          labelId="demo-simple-select-label"
                          id="demo-simple-select"
                          value={sensorCategoryId}
                          label="Sensor Category"
                          required
                          onChange={(e) => {
                            onSensorCategoryChange(e);
                          }}
                        
                        >
                          
                          {sensorCategoryList.map((data) => {
                            return (
                              <MenuItem value={data.id}>{data.sensorName}</MenuItem>
                            );
                          })}
                        </Select>
                      </FormControl>
                    </Box>
                  </Grid>
                  <Grid
                    sx={{ mt: 0, padding: 0 }}
                    item
                    xs={12}
                    sm={6}
                    md={6}
                    lg={6}
                    xl={6}
                  >
                    <div className="rounded-md -space-y-px">
                      <TextField
                        sx={{ marginTop: 0 }}
                        value={sensorName}
                        onBlur={() => validateForNullValue(sensorName, 'sensorName')}
                        onChange={(e) => {
                          setSensorName(e.target.value);
                        }}
                        margin="normal"
                        required
                        id="outlined-required"
                        label="Name of the sensor"
                        fullWidth
                        error={errorObject?.sensorName?.errorStatus}
                        helperText={errorObject?.sensorName?.helperText}
                        autoComplete="off"
                        InputLabelProps={{
                          shrink:'true'
                        }}
                      />
                    </div>
                  </Grid>
                  <Grid
                    sx={{ mt: 0, padding: 0 }}
                    item
                    xs={12}
                    sm={6}
                    md={6}
                    lg={6}
                    xl={6}
                  >
                    <div className="rounded-md -space-y-px">
                      <TextField
                        sx={{ marginTop: 0 }}
                        value={manufacturer}
                        onBlur={() => validateForNullValue(manufacturer, 'manufacturer')}
                        onChange={(e) => {
                          setManufacturer(e.target.value);
                        }}
                        margin="normal"
                        required
                        id="outlined-required"
                        label="Manufacturer"
                        fullWidth
                        error={errorObject?.manufacturer?.errorStatus}
                        helperText={errorObject?.manufacturer?.helperText}
                        autoComplete="off"
                        InputLabelProps={{
                          shrink:'true'
                        }}
                      />
                    </div>
                  </Grid>
                  <Grid
                    sx={{ mt: 0, padding: 0 }}
                    item
                    xs={12}
                    sm={6}
                    md={6}
                    lg={6}
                    xl={6}
                  >
                    <div className="rounded-md -space-y-px">
                      <TextField
                        sx={{ marginTop: 0 }}
                        value={partId}
                        onBlur={() => validateForNullValue(partId, 'partId')}
                        onChange={(e) => {
                          setPartId(e.target.value);
                        }}
                        margin="normal"
                        required
                        id="outlined-required"
                        label="Part Id"
                        fullWidth
                        error={errorObject?.partId?.errorStatus}
                        helperText={errorObject?.partId?.helperText}
                        autoComplete="off"
                        InputLabelProps={{
                          shrink:'true'
                        }}
                      />
                    </div>
                  </Grid>
                  <Grid
                    sx={{ mt: 0, padding: 0, alignSelf: 'center' }}
                    item
                    xs={12}
                    sm={6}
                    md={6}
                    lg={6}
                    xl={6}
                  >
                    <FormControl fullWidth margin="normal" sx={{ marginTop: 0 }}>
                      <InputLabel id="demo-simple-select-label3">Alarm Type</InputLabel>
                      <Select
                        sx={{ marginTop: 0 }}
                        labelId="demo-simple-select-label3"
                        id="demo-simple-select3"
                        value={alarm}
                        label="Alarm Type"
                        required
                        onChange={(e) => {
                          setAlarm(e.target.value);
                        }}
                      >
                        <MenuItem value="Latch">Latch</MenuItem>
                        <MenuItem value="UnLatch">UnLatch</MenuItem>
                      </Select>
                    </FormControl>
                  </Grid>
                  {/* <Grid
                    sx={{ mt: 0, padding: 0 }}
                    item
                    xs={12}
                    sm={6}
                    md={4}
                    lg={4}
                    xl={4}
                  >
                    <div className="rounded-md -space-y-px">
                      <FormControl fullWidth>
                        <InputLabel id="demo-simple-select-label">Relay Output</InputLabel>
                        <Select
                          labelId="demo-simple-select-label"
                          label="Relay Output"
                          value={relayOutput}
                          onChange={(e) => {
                            setRelayOutput(e.target.value);
                          }}
                        >
                          <MenuItem value="ON">Enable</MenuItem>
                          <MenuItem value="OFF">Disable</MenuItem>
                        </Select>
                      </FormControl>
                    </div>
                  </Grid> */}
                  <Grid
                    sx={{ mt: 0, padding: 0 }}
                    item
                    xs={12}
                    sm={6}
                    md={6}
                    lg={6}
                    xl={6}
                  >
                    <div className="rounded-md -space-y-px">
                      <FormControl fullWidth>
                        <InputLabel id="demo-simple-select-label" >Bump Test</InputLabel>
                        <Select
                          labelId="demo-simple-select-label"
                          label="Bump Test"
                          value={bumpTestRequired}
                          onChange={(e) => {
                            setBumpTestRequired(e.target.value);
                          }}
                        >
                          <MenuItem value="ON">Required</MenuItem>
                          <MenuItem value="OFF">Not required</MenuItem>
                        </Select>
                      </FormControl>
                    </div>
                  </Grid>
                  {
                  
                    bumpTestRequired === 'ON' || userDetails.userRole !== 'systemSpecialist'  ? (
                    <>
                      <Grid
                        sx={{ mt: 0, padding: 0 }}
                        item
                        xs={12}
                        sm={6}
                        md={6}
                        lg={6}
                        xl={6}
                      >
                      <TextField
                          sx={{ marginTop: 0 }}
                          margin="dense"
                          id="outlined-required"
                          label="Percentage deviation for Zero Check"
                          // placeholder='Percentage deviation for Zero Check'
                          defaultValue=""
                          fullWidth
                          value={zeroCheckValue}
                          // required
                          onChange={(e)=>setZeroCheckValue(e.target.value)}
                          // onBlur={() => validatepercentageDeviation(percentageDeviation)}
                          onBlur={() => validateForNullValue(zeroCheckValue, 'zeroCheckValue')}
                          autoComplete="off"
                          error={errorObject?.zeroCheckValue?.errorStatus}
                          helperText={errorObject?.zeroCheckValue?.helperText}
                          InputLabelProps={{
                            shrink:'true'
                          }}
                        />
                      </Grid>
                      

                      <Grid
                        sx={{ mt: 0, padding: 0 }}
                        item
                        xs={12}
                        sm={6}
                        md={6}
                        lg={6}
                        xl={6}
                      >
                        <TextField
                          sx={{ marginTop: 0 }}
                          margin="dense"
                          id="outlined-required"
                          label="Percentage deviation for Span Check"
                          // placeholder='Percentage deviation for Span Check'
                          defaultValue=""
                          fullWidth
                         
                          value={spanCheckValue}
                          // required
                          onChange={(e)=>setSpanCheckValue(e.target.value)}
                          // onBlur={() => validatepercentageDeviation(percentageDeviation)}
                          onBlur={() => validateForNullValue(spanCheckValue, 'spanCheckValue')}
                          autoComplete="off"
                          error={errorObject?.spanCheckValue?.errorStatus}
                          helperText={errorObject?.spanCheckValue?.helperText}
                          InputLabelProps={{
                            shrink:'true'
                          }}
                        />
                      </Grid>
                    </>
                    ) : (
                      <>
                        {/* JSX to be rendered when bumpTestRequired is not 'NO' */}
                      </>
                    )}
                  <Grid
                    sx={{ mt: 0, padding: 0 }}
                    item
                    xs={12}
                    sm={6}
                    md={6}
                    lg={6}
                    xl={6}
                  >
                    <Box>
                      <FormControl fullWidth margin="normal" sx={{ marginTop: 0 }}>
                        <InputLabel id="demo-simple-select-label">
                          Sensor Output
                        </InputLabel>
                        <Select
                          labelId="demo-simple-select-label"
                          id="demo-simple-select"
                          value={sensorOutput}
                          label="Sensor Output"
                          onChange={(e) => {
                            setSensorOutput(e.target.value);
                          }}
                        >
                          <MenuItem value="Digital">Digital</MenuItem>
                          <MenuItem value="Inbuilt">Inbuilt </MenuItem>
                          <MenuItem value="Analog">Analog</MenuItem>
                          <MenuItem value="Modbus">Modbus</MenuItem>
                        </Select>
                      </FormControl>
                    </Box>
                  </Grid>
                </Grid>
                {/* eslint-disable-next-line */}
                {sensorOutput === 'Analog' ? (
                  <Analog
                    errorObject={errorObject}
                    setErrorObject={setErrorObject}
                    units={units}
                    unitsList={unitsList}
                    setUnits={setUnits}
                    // relayOutput={relayOutput}
                    bumpTestRequired={bumpTestRequired}
                    // setRelayOutput={setRelayOutput}
                    setBumpTestRequired={setBumpTestRequired}
                    sensorType={sensorType}
                    setSensorType={setSensorType}
                    minRatedReading={minRatedReading}
                    setMinRatedReading={setMinRatedReading}
                    minRatedReadingChecked={minRatedReadingChecked}
                    setMinRatedReadingChecked={setMinRatedReadingChecked}
                    minRatedReadingScale={minRatedReadingScale}
                    setMinRatedReadingScale={setMinRatedReadingScale}
                    maxRatedReading={maxRatedReading}
                    setMaxRatedReading={setMaxRatedReading}
                    maxRatedReadingChecked={maxRatedReadingChecked}
                    setMaxRatedReadingChecked={setMaxRatedReadingChecked}
                    maxRatedReadingScale={maxRatedReadingScale}
                    setMaxRatedReadingScale={setMaxRatedReadingScale}
                  />
                ) : sensorOutput === 'Modbus' ? (
                  <Modbus
                    errorObject={errorObject}
                    setErrorObject={setErrorObject}
                    units={units}
                    unitsList={unitsList}
                    setUnits={setUnits}
                    // relayOutput={relayOutput}
                    bumpTestRequired={bumpTestRequired}
                    // setRelayOutput={setRelayOutput}
                    setBumpTestRequired={setBumpTestRequired}
                    sensorType={sensorType}
                    setSensorType={setSensorType}
                    minRatedReading={minRatedReading}
                    setMinRatedReading={setMinRatedReading}
                    minRatedReadingChecked={minRatedReadingChecked}
                    setMinRatedReadingChecked={setMinRatedReadingChecked}
                    minRatedReadingScale={minRatedReadingScale}
                    setMinRatedReadingScale={setMinRatedReadingScale}
                    maxRatedReading={maxRatedReading}
                    setMaxRatedReading={setMaxRatedReading}
                    maxRatedReadingChecked={maxRatedReadingChecked}
                    setMaxRatedReadingChecked={setMaxRatedReadingChecked}
                    maxRatedReadingScale={maxRatedReadingScale}
                    setMaxRatedReadingScale={setMaxRatedReadingScale}
                    slaveId={slaveId}
                    setSlaveId={setSlaveId}
                    registerId={registerId}
                    setRegisterId={setRegisterId}
                    length={length}
                    setLength={setLength}
                    registerType={registerType}
                    setRegisterType={setRegisterType}
                    conversionType={conversionType}
                    setConversionType={setConversionType}
                    ipAddress={ipAddress}
                    setIpAddress={setIpAddress}
                    subnetMask={subnetMask}
                    setSubnetMask={setSubnetMask}
                  />
                ) : (
                  ''
                )}
              </>
            )
            : ''}
          {nextPage === true
            ? ''
            : (
              <>
                <StelTWA
                  isStel={isStel}
                  setIsStel={setIsStel}
                  isAQI={isAQI}
                  setIsAQI={setIsAQI}
                  stelDuration={stelDuration}
                  setStelDuration={setStelDuration}
                  stelType={stelType}
                  setStelType={setStelType}
                  stelLimit={stelLimit}
                  setStelLimit={setStelLimit}
                  stelAlert={stelAlert}
                  setStelAlert={setStelAlert}
                  twaDuration={twaDuration}
                  setTwaDuration={setTwaDuration}
                  twaType={twaType}
                  setTwaType={setTwaType}
                  twaStartTime={twaStartTime}
                  setTwaStartTime={setTwaStartTime}
                  stelStartTime={stelStartTime}
                  setStelStartTime={setStelStartTime}
                  twaLimit={twaLimit}
                  setTwaLimit={setTwaLimit}
                  twaAlert={twaAlert}
                  setTwaAlert={setTwaAlert}
                  parmGoodMinScale={parmGoodMinScale}
                  setParmGoodMinScale={setParmGoodMinScale}
                  parmGoodMaxScale={parmGoodMaxScale}
                  setParmGoodMaxScale={setParmGoodMaxScale}
                  parmSatisfactoryMinScale={parmSatisfactoryMinScale}
                  setParmSatisfactoryMinScale={setParmSatisfactoryMinScale}
                  parmSatisfactoryMaxScale={parmSatisfactoryMaxScale}
                  setParmSatisfactoryMaxScale={setParmSatisfactoryMaxScale}
                  parmModerateMinScale={parmModerateMinScale}
                  setParmModerateMinScale={setParmModerateMinScale}
                  parmModerateMaxScale={parmModerateMaxScale}
                  setParmModerateMaxScale={setParmModerateMaxScale}
                  parmPoorMinScale={parmPoorMinScale}
                  setParmPoorMinScale={setParmPoorMinScale}
                  parmPoorMaxScale={parmPoorMaxScale}
                  setParmPoorMaxScale={setParmPoorMaxScale}
                  parmVeryPoorMinScale={parmVeryPoorMinScale}
                  setParmVeryPoorMinScale={setParmVeryPoorMinScale}
                  parmVeryPoorMaxScale={parmVeryPoorMaxScale}
                  setParmVeryPoorMaxScale={setParmVeryPoorMaxScale}
                  parmSevereMinScale={parmSevereMinScale}
                  setParmSevereMinScale={setParmSevereMinScale}
                  parmSevereMaxScale={parmSevereMaxScale}
                  setParmSevereMaxScale={setParmSevereMaxScale}
                />
                {sensorOutput === 'Digital' || sensorOutput ===  'Inbuilt' ? '' : (
                  <SensorAlertRange
                    errorObject={errorObject}
                    setErrorObject={setErrorObject}
                    criticalMinValue={criticalMinValue}
                    setCriticalMinValue={setCriticalMinValue}
                    criticalMaxValue={criticalMaxValue}
                    setCriticalMaxValue={setCriticalMaxValue}
                    warningMinValue={warningMinValue}
                    setWarningMinValue={setWarningMinValue}
                    warningMaxValue={warningMaxValue}
                    setWarningMaxValue={setWarningMaxValue}
                    outofrangeMinValue={outofrangeMinValue}
                    setOutofrangeMinValue={setOutofrangeMinValue}
                    outofrangeMaxValue={outofrangeMaxValue}
                    setOutofrangeMaxValue={setOutofrangeMaxValue}
                    criticalRefMinValue={criticalRefMinValue} 
                    criticalRefMaxValue={criticalRefMaxValue} 
                    warningRefMinValue={warningRefMinValue} 
                    warningRefMaxValue={warningRefMaxValue} 
                    outofrangeRefMinValue={outofrangeRefMinValue} 
                    outofrangeRefMaxValue={outofrangeRefMaxValue}
                  />
                )}
              </>
            )}
          <Grid
            container
            spacing={1}
            sx={{ mt: 0 }}
            xs={12}
            sm={12}
            md={12}
            lg={12}
            xl={12}
          >
            <Grid
              sx={{ float: 'right', textAlign: 'right' }}
              item
              xs={12}
              sm={12}
              md={12}
              lg={12}
              xl={12}
            >
              {nextPage !== true
                && (
                  <Button
                  style={{
                    background: 'rgb(19 60 129)',}}
                  sx={{
                    m: 2,
                    color: 'white',
                    padding: "8px 27px",
                    marginTop: '18px',
                    marginRight: '10px',
                    marginBottom: '35px',
                    fontSize: '13px',
                    borderRadius: '10px',
                    fontWeight: '600',
                    fontFamily: 'customfont',
                    letterSpacing: '1px',
                    "&.Mui-disabled": {
                      background: "#eaeaea",
                      color: "#c0c0c0"
                    }
                  }}
                    size="large"
                    type="submit"
                    disabled={
                      errorObject?.sensorName?.errorStatus
                    || errorObject?.manufacturer?.errorStatus
                    || errorObject?.partId?.errorStatus
                    || errorObject?.units?.errorStatus
                    || errorObject?.minRatedReading?.errorStatus
                    || errorObject?.minRatedReadingScale?.errorStatus
                    || errorObject?.maxRatedReading?.errorStatus
                    || errorObject?.maxRatedReadingScale?.errorStatus
                    || errorObject?.ipAddress?.errorStatus
                    || errorObject?.subnetMask?.errorStatus
                    || errorObject?.slaveId?.errorStatus
                    || errorObject?.registerId?.errorStatus
                    }
                  >
                    {isAddButton ? 'ADD' : 'UPDATE'}
                  </Button>
                )}
              <Button
                style={{
                  background: 'rgb(19 60 129)',}}
                sx={{
                  m: 2,
                  color: 'white',
                  padding: "8px 27px",
                  marginTop: '18px',
                  marginRight: '10px',
                  marginBottom: '35px',
                  fontSize: '13px',
                  borderRadius: '10px',
                  fontWeight: '600',
                  fontFamily: 'customfont',
                  letterSpacing: '1px',
                  "&.Mui-disabled": {
                    background: "#eaeaea",
                    color: "#c0c0c0"
                  }
                }}
                onClick={() => {
                  if(nextButton === 'Next'){
                    validateNextButton();
                  } else {
                    setNextPage((oldValue) => {
                      /* eslint-disable-next-line */
                      oldValue === true ? setNextButton('Back') : setNextButton('Next');
                      return !oldValue;
                    });
                  }
                }}
              >
                {nextButton}
              </Button>
              <Button
                style={{
                  background: 'rgb(19 60 129)',}}
                sx={{
                  m: 2,
                  color: 'white',
                  padding: "8px 27px",
                  marginTop: '18px',
                  marginRight: '10px',
                  marginBottom: '35px',
                  fontSize: '13px',
                  borderRadius: '10px',
                  fontWeight: '600',
                  fontFamily: 'customfont',
                  letterSpacing: '1px',
                  "&.Mui-disabled": {
                    background: "#eaeaea",
                    color: "#c0c0c0"
                  }
                }}
                onClick={() => {
                  setErrorObject({});
                  setOpen(false);
                }}
              >
                Cancel
              </Button>
            </Grid>
          </Grid>
          {nextPage === true
            ? ''
            : (
              <div className="float-right" />
            )}
        </DialogContent>
      </form>
      <NotificationBar
        handleClose={handleClose}
        notificationContent={openNotification.message}
        openNotification={openNotification.status}
        type={openNotification.type}
      />
    </div>
  );
}

export default SensorConfig;
