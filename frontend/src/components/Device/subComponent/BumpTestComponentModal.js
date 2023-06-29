import {
  Button, Dialog, DialogContent, DialogTitle, FormControl, InputLabel, Typography, MenuItem, Select,
  TextField, FormControlLabel, Radio, RadioGroup, Chip,
} from '@mui/material';
import React, { useEffect, useState } from 'react';
import DialogActions from '@mui/material/DialogActions';
import Grid from '@mui/material/Grid';
import { DataGrid } from '@mui/x-data-grid';
import LoadingButton from '@mui/lab/LoadingButton';
import RestartAltRoundedIcon from '@mui/icons-material/RestartAltRounded';
import { AddCategoryValidate } from '../../../validation/formValidation';
import { BumpTestAddService, BumpTestFetchService, ChangeDeviceMode, GasCylinderFetchService } from '../../../services/LoginPageService';
import { BumpTestData } from '../../../services/BumpTestServicePage';
import NotificationBar from '../../notification/ServiceNotificationBar';
import { BumptestValidate } from '../../../validation/formValidation';
import { Cancel, CheckCircle, CheckCircleOutline, Error, WarningAmber } from '@mui/icons-material';
import { currentDateValidator } from '../../../utils/helperFunctions';
/* eslint-disable no-plusplus */

const convertDateTime = (value) => {
  const spaceSplit = value.split(' ');
  const dateSplit = spaceSplit[0].split('-');
  const date = `${dateSplit[2]}-${dateSplit[1]}-${dateSplit[0]}`;
  return date;
};

const columns = [
  {
    field: 'calibrationDate',
    headerName: ' BumpTest Date',
    minWidth: 150,
    flex: 1,
    headerAlign: 'center',
    align : 'center',
    renderCell: (params) => (
      <Typography>
        {
          /* eslint-disable-next-line */
          convertDateTime(params.value)
        }
      </Typography>
    ),
  },
  {
    field: 'typeCheck',
    headerName: 'Type check',
    minWidth: 100,
    flex: 1,
    headerAlign: 'center',
    align : 'center',
  },
  {
    field: 'displayedValue',
    headerName: ' Displayed Value',
    minWidth: 150,
    flex: 1,
    headerAlign: 'center',
    align : 'center',
  },
  {
    field: 'percentageDeviation',
    headerName: ' Percentage Deviation Value',
    minWidth: 200,
    flex: 1,
    headerAlign: 'center',
    align : 'center',
  },
  {
    field: 'result',
    headerName: 'Result',
    minWidth: 150,
    flex: 1,
    headerAlign: 'center',
    align : 'center',
    editable: true,
    renderCell: (params) => (
      <Typography sx={{ color: params.value === 'Pass' ? 'green' : params.value === 'Fail' ? 'red' : 'blue' }}>
        {params.value}
      </Typography>
    ),
  },
];

/* eslint-disable-next-line */
function BumpTestComponentModal({
  open, setOpen, isAddButton, setRefreshData, deployedSensorTagList, device_id,
}) {
  const [sensorTagName, setSensorTagName] = useState('');
  // const [gasCylinderName, setGasCylinderName] = useState({});
  // const [expiryDate, setExpiryDate] = useState(false);
  const [lastDueDate, setLastDueDate] = useState('');
  const [typeCheck, setTypeCheck] = useState('zeroCheck');
  const [percentageConcentrationGas, setPercentrationConcentrationGas] = useState(0);
  const [durationPeriod, setDurationPeriod] = useState('');
  const [displayedValue, setDisplayedValue] = useState('');
  const [percentageDeviation, setPercentageDeviation] = useState('');
  const [nextDueDate, setNextDueDate] = useState('');
  const [result, setResult] = useState('NA');
  const [deployedSensorList, setDeployedSensorList] = useState([]);
  // const [gasCylinderList, setGasCylinderList] = useState([]);
  const [bumpTestData, setBumpTestData] = useState([]);
  const [bumpTestPercentage,setBumpTestPercentage] = useState(0);
  const [loading, setLoading] = useState(false);
  const [cylinderDialog, setCylinderDialog] = useState(false);
  const [progress, setProgress] = useState('start');
  let i = 0;
  let j = 0;
  /* eslint-disable-next-line */
  const [errorObject, setErrorObject] = useState({});
  const [bumpData, setBumpData] = useState([]);
  const [inputDisable, setInputDisable] = useState(false);
  const [enableStart, setEnableStart] = useState(true);
  const [openNotification, setNotification] = useState({
    status: false,
    type: 'error',
    message: '',
  });

  useEffect(() => {
    loadData();
    // GasCylinderFetchService(handleGasSuccess, handleGasException);
  }, [deployedSensorTagList]);

  // const handleGasSuccess = (dataObject) =>{
  //   setGasCylinderList(dataObject.data || []);
  // }

  // const handleGasException = (errorObject, errorMessage) =>{
    
  // }

  const loadData = () => {
    setDeployedSensorList(deployedSensorTagList || []);
  };

  const reset = () => {
    setEnableStart(true);
    setSensorTagName('');
    setLastDueDate('');
    setTypeCheck('zeroCheck');
    setPercentrationConcentrationGas(0);
    setDurationPeriod('');
    setDisplayedValue('');
    setPercentageDeviation('');
    setNextDueDate('');
    setResult('NA');
    setDeployedSensorList([]);
    setBumpTestData([]);
    setErrorObject({});
    setBumpData([]);
  };

  const confirmGasCylinderExpiry = () =>{
    setCylinderDialog(true);
  };

  const getBumpData = () => {
    setDisplayedValue('');
    setPercentageDeviation('');
    setResult('NA');
    setLoading(true);
    setProgress('started');
    setBumpData([]);
    setInputDisable(true);
    ChangeDeviceMode({ id: device_id, deviceMode: 'bumpTest' }, modeChangeHandleSuccess, modeChangeHandleException);
  };

  const modeChangeHandleException = (errorObject, errorMessage) => {
    setNotification({
      status: true,
      type: 'error',
      message: errorMessage,
    });
    setInputDisable(false);
    setProgress('start');
    setLoading(false);
  };

  const modeChangeHandleSuccess = () => {
    setRefreshData((oldvalue) => !oldvalue);
    const DurationSec = durationPeriod;

    /* eslint-disable-next-line */
    const callCount = parseInt(DurationSec / 2);
    let count = 0;
    let dataCount = 0;

    function bumpTestDataCall() {
      
      if (i === j) {
        i++;
        BumpTestData({ sensorTagName }, getBumpTestDataSuccess, getBumpTestDataHandleException);
        if (count === callCount) {
          setInputDisable(false);
          clearInterval(myVar);
          /* eslint-disable-next-line */
          const dataList = bumpData.length;
          let total = 0;
          let pcgValPowOfTwo = 0;
          /* eslint-disable-next-line */
          for (let i = 0; i < dataList; i++) {
            if (bumpData[i] !== 'NA') {
              let pcgVal = 0;
              /* eslint-disable-next-line */
              pcgVal = parseInt(Number(percentageConcentrationGas)) - parseInt(bumpData[i]);
              pcgValPowOfTwo = pcgVal * pcgVal;
              total += pcgValPowOfTwo;
              /* eslint-disable-next-line */
              dataCount++;
            }
          }
          if (dataCount < 3) {
            setPercentageDeviation('NA');
            setLoading(false);
            setProgress('start');
          } else {
            let avg = 0;
            avg = total / dataList;
            setPercentageDeviation(Math.sqrt(avg).toFixed(2));
            testBumptestresult(Math.sqrt(avg).toFixed(2));
            setLoading(false);
            setProgress('start');
          }
          enableDeviceMode(device_id);
        }
        /* eslint-disable-next-line */
        count++;
      }
    }

    const myVar = setInterval(() => {
      bumpTestDataCall();
    }, 2000);
  };

  const testBumptestresult = (testResult) => {
    if(testResult === 'NA'){
      setResult('NA');
    } else{
      if(testResult >=0 && testResult <= bumpTestPercentage){
        setResult('Pass');
        setNotification({
          status: true,
          type: 'success',
          message: 'Bump Test passed successfully',
        });
      } else {
        setResult('Fail');
        setNotification({
          status: true,
          type: 'error',
          message: 'Bump Test failed',
        });
      }
    }
  }
  const enableDeviceMode = (id) => {
    ChangeDeviceMode({ id, deviceMode: 'enabled' }, modeHandleSuccess, modeChangeHandleException);
  };
  const modeHandleSuccess = () => {
    setRefreshData((oldvalue) => !oldvalue);
  };

  /* eslint-disable-next-line */
  const getBumpTestResultData = (data) => {
    setEnableStart(false);
    setLastDueDate('');
    setTypeCheck('zeroCheck');
    setPercentrationConcentrationGas('');
    setDurationPeriod('');
    setDisplayedValue('');
    setPercentageDeviation('');
    setNextDueDate('');
    setResult('NA');
    setBumpTestData([]);
    setErrorObject({});
    setBumpData([]);
    BumpTestFetchService({ sensorTagName: data }, getBumpTestResultDataSuccess, getBumpTestResultDataHandleException);
  };

  const getBumpTestResultDataSuccess = (dataObject) => {
    if (dataObject.nextDueDate === '') {
      setLastDueDate('');
      setBumpTestData([]);
    } else {
      setLastDueDate(new Date(dataObject.nextDueDate).toLocaleDateString());
      setBumpTestData(dataObject.data);
      if(typeCheck === 'zeroCheck')
      {
        setBumpTestPercentage(dataObject?.zeroCheckValue || 0);
      }else if(typeCheck === 'SpanCheck')
      {
        setBumpTestPercentage(dataObject?.spanCheckValue || 0);
      }else{
        setBumpTestPercentage(0);
      }
          
     
    }
  };
  /* eslint-disable-next-line */
  const getBumpTestResultDataHandleException = (dataObject, errorObject) => {
  };

  const getBumpTestDataSuccess = (dataObject) => {
    j++;
    setDisplayedValue(dataObject.data.LAST.toFixed(2));
    bumpData.push(dataObject.data.LAST);
  };
  /* eslint-disable-next-line */
  const getBumpTestDataHandleException = (dataObject, errorObject) => {
  };
  /* eslint-disable-next-line */
  const validateForNullValue = (value, type) => {
    BumptestValidate(value, type, setErrorObject);
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (isAddButton) {
      if (durationPeriod == '') {
        validateForNullValue(durationPeriod, 'durationPeriod')
      } else {
        setInputDisable(true);
        BumpTestAddService({
          /* eslint-disable-next-line */
          sensorTagName, lastDueDate, typeCheck, percentageConcentrationGas, durationPeriod, displayedValue, nextDueDate, result, percentageDeviation, device_id,
        }, handleSuccess, handleException);
        
      }
    }
  };

  const handleSuccess = (dataObject) => {
    setInputDisable(false);
    setEnableStart(false);
    setLastDueDate('');
    setTypeCheck('zeroCheck');
    setPercentrationConcentrationGas('');
    setDurationPeriod('');
    setDisplayedValue('');
    setPercentageDeviation('');
    setNextDueDate('');
    setResult('NA');
    setErrorObject({});
    setBumpData([]);
    setNotification({
      status: true,
      type: 'success',
      message: dataObject.message,
    });
    setRefreshData((oldvalue) => !oldvalue);
    setTimeout(() => {
      handleClose();
    }, 3000);
    BumpTestFetchService({ sensorTagName }, getBumpTestResultDataSuccess, getBumpTestResultDataHandleException);
  };
  /* eslint-disable-next-line */
  const handleException = (errorObject, errorMessage) => {
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

  const onCancel = () => {
    ChangeDeviceMode({ id: device_id, deviceMode: 'enabled' }, cancelHandleSuccess, modeChangeHandleException);
    setPercentageDeviation('');
    setCylinderDialog(false);
    setDisplayedValue('');
    setLoading(false);
    loadingPosition ='end';
    setProgress('start');
    // setExpiryDate(false);
    // setGasCylinderName({});
   
  
  };
  const cancelHandleSuccess = () => {
    setRefreshData((oldvalue) => !oldvalue);
    setOpen(false);
    setErrorObject({});
    loadData();
    reset();
  };

  // const checkGasExpiryDate = (data) =>{
  //   setGasCylinderName(data);
  //   // setExpiryDate(data.expiryDate);
  //   var todayDate = new Date();
  //   let currentDate = todayDate.getFullYear()+'-'+('0'+(todayDate.getMonth()+1)).slice(-2)+'-'+ ('0' + todayDate.getDate()).slice(-2);

  //   const presentDay = new Date(currentDate);
  //   const expiryday = new Date(data.expiryDate);
  //   console.log('Expiry Date :', data.expiryDate);
  //   console.log('Current Date :', currentDate);
  //   if(presentDay >= expiryday){
  //     setCylinderDialog(true);
  //     setExpiryDate(false);
  //     // setNotification({
  //     //   status: true,
  //     //   type: 'error',
  //     //   message: 'Gas Cylinder Expired...!',
  //     // });
  //     // setTimeout(() => {
  //     //   handleClose();
  //     // }, 3000);
  //   } else{
  //     setExpiryDate(true);
  //   }
  // }

  return (
    <Dialog
      fullWidth
      maxWidth="md"
      sx={{ '& .MuiDialog-paper': { width: '80%', maxHeight: '100%' } }}
      open={open}
    >
      <form onSubmit={handleSubmit}>
        <DialogTitle>
          {isAddButton ? 'Bump Test' : ''}
        </DialogTitle>
        <DialogContent>
          <div className="flex items-center justify-between gap-3">
            <Grid container spacing={1} sx={{ mt: 0 }}>
              <Grid
                sx={{ mt: 0, padding: 0 }}
                item
                xs={12}
                sm={6}
                md={6}
                lg={4}
                xl={4}
              >
                <FormControl fullWidth sx={{ mt: 0, padding: 0 }}>
                  <InputLabel id="demo-simple-select-label">Deployed Sensors</InputLabel>
                  <Select
                    required
                    labelId="demo-simple-select-label"
                    id="demo-simple-select"
                    value={sensorTagName}
                    label="Deployed Sensors"
                    onChange={(e) => {
                      setSensorTagName(e.target.value);
                      getBumpTestResultData(e.target.value);
                    }}
                  >
                    {/* eslint-disable-next-line */}
                    {deployedSensorList.map((data, index) => (
                      /* eslint-disable-next-line */
                      <MenuItem value={data.sensorTag} key={index}>{data.sensorTag}</MenuItem>
                    ))}
                  </Select>
                </FormControl>
              </Grid>
              <Grid
                sx={{ mt: 0, padding: 0 }}
                item
                xs={12}
                sm={6}
                md={6}
                lg={4}
                xl={4}
              >
                <TextField
                  sx={{ marginTop: 0 }}
                  margin="dense"
                  id="outlined-required"
                  label="Next Due Date"
                  defaultValue=""
                  fullWidth
                  type="text"
                  disabled="true"
                  value={lastDueDate}
                  required
                  onChange={(e) => { setLastDueDate(e.target.value); }}
                  autoComplete="off"
                />
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
                <FormControl fullWidth sx={{ mt: 0, padding: 0 }}>
                  <InputLabel id="demo-simple-select-label">Gas Cylinders</InputLabel>
                  <Select
                    required
                    labelId="demo-simple-select-label"
                    id="demo-simple-select"
                    value={gasCylinderName}
                    label="Gas Cylinders"
                    onChange={(e) => {
                      checkGasExpiryDate(e.target.value);
                    }}
                  >
                    {gasCylinderList.map((data, index) => (
                      <MenuItem value={data} key={index}>{data.gasCylinderName}</MenuItem>
                    ))}
                  </Select>
                </FormControl>
              </Grid> */}
              <Grid
                sx={{ mt: 0, padding: 0 }}
                item
                xs={12}
                sm={6}
                md={4}
                lg={4}
                xl={4}
              >
                <FormControl className="float-left">
                  <RadioGroup
                    row
                    aria-labelledby="demo-row-radio-buttons-group-label"
                    name="row-radio-buttons-group"
                    value={typeCheck}
                    onClick={(e) => {
                      setTypeCheck(e.target.value);
                      setPercentrationConcentrationGas(e.target.value === 'zeroCheck' ? 0 : '');
                      if(e.target.value === 'zeroCheck'){
                        setErrorObject((oldValue)=>{
                          return {...oldValue, percentageConcentrationGas: {}}
                        });
                      } else if (e.target.value === 'SpanCheck'){
                        validateForNullValue(percentageConcentrationGas, 'percentageConcentrationGas');
                      }
                    }}
                  >
                    <FormControlLabel value="zeroCheck" control={<Radio required />} label="Zero Check" />
                    <FormControlLabel value="SpanCheck" control={<Radio required />} label="Span Check" />
                  </RadioGroup>
                </FormControl>
              </Grid>
              <Grid
                sx={{ mt: 0, padding: 0 }}
                item
                xs={12}
                sm={6}
                md={4}
                lg={4}
                xl={4}
              >
                <TextField
                  sx={{ marginTop: 0 }}
                  margin="dense"
                  id="outlined-required"
                  label="Percentage Concentration Of Gas"
                  defaultValue=""
                  fullWidth
                  disabled={typeCheck === 'zeroCheck'}
                  value={typeCheck === 'zeroCheck' ? 0 : percentageConcentrationGas}
                  required
                  onChange={(e) => { setPercentrationConcentrationGas(e.target.value); }}
                  onBlur={() => validateForNullValue(percentageConcentrationGas, 'percentageConcentrationGas')}
                  autoComplete="off"
                  error={errorObject?.percentageConcentrationGas?.errorStatus}
                  helperText={errorObject?.percentageConcentrationGas?.helperText}
                />
              </Grid>
              <Grid
                sx={{ mt: 0, padding: 0 }}
                item
                xs={12}
                sm={6}
                md={4}
                lg={4}
                xl={4}
              >
                <TextField
                  sx={{ marginTop: 0 }}
                  value={durationPeriod}
                  id="dense"
                  label="Duration (sec)"
                  autoComplete="off"
                  fullWidth
                  type="text"
                  onBlur={() => validateForNullValue(durationPeriod, 'durationPeriod')}
                  onChange={(e) => {
                    setDurationPeriod(e.target.value);
                  }}
                  InputLabelProps={{
                    shrink: true,
                  }}
                  margin="normal"
                  error={errorObject?.durationPeriod?.errorStatus}
                  helperText={errorObject?.durationPeriod?.helperText}
                />
              </Grid>
            </Grid>
          </div>
          <div style={{
            alignItems:'center'
          }}>
            <Grid container spacing={1} sx={{ mt: 0 }} style={{
              alignContent: 'center',
              display: 'flex',
              justifyContent: 'space-evenly',
              alignItems: 'center',
            }}>
              <Grid
                sx={{ mt: 0, padding: 0 }}
                item
                xs={12}
                sm={2}
                md={2}
                lg={2}
                xl={2}
              >
                <LoadingButton
                  disabled={errorObject?.durationPeriod?.errorStatus || errorObject?.percentageConcentrationGas?.errorStatus || enableStart } // || expiryDate === false
                  onClick={(e) => {
                    if (durationPeriod == '') {
                      validateForNullValue(durationPeriod, 'durationPeriod');
                    }else if (percentageConcentrationGas === '' && typeCheck === 'SpanCheck') {
                      validateForNullValue(percentageConcentrationGas, 'percentageConcentrationGas');
                    }else {
                      // getBumpData(e);
                      confirmGasCylinderExpiry();
                    }
                  }}
                  endIcon={<RestartAltRoundedIcon />}
                  loading={loading}
                  loadingPosition="end"
                  variant="contained"
                >
                  {progress}
                </LoadingButton>
              </Grid>
              <Grid
                sx={{ mt: 0, padding: 0 }}
                item
                xs={12}
                sm={4}
                md={4}
                lg={4}
                xl={4}
              >
                <h3>
                  Display Value:
                  <b>
                    {' '}
                    {displayedValue}
                  </b>
                </h3>
                <h3>
                  Percentage Deviation:
                  <b>{percentageDeviation}</b>
                </h3>
              </Grid>
              <Grid
                sx={{ mt: 0, padding: 0 }}
                item
                xs={12}
                sm={4}
                md={4}
                lg={4}
                xl={4}
              >
                <span style={{marginRight: '2px'}}>Result :</span>
                <Chip label={result} 
                  icon={result === 'NA' ? <Error /> : result === 'Pass' ? <CheckCircle /> : <Cancel />}
                  color={result === 'NA' ? 'primary' : result === 'Pass' ? 'success' : 'error'}
                  variant='outlined' 
                  // style={{
                  //   display: result === '' && 'none'
                  // }}
                />
              </Grid>
            </Grid>
          </div>
          <div className="flex items-center justify-between gap-2">
            <Grid container spacing={1} sx={{ mt: 0 }}>
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
                  margin="normal"
                  id="outlined-required"
                  label="Set Next Due Date"
                  defaultValue=""
                  fullWidth
                  type="date"
                  disabled={inputDisable === true}
                  required
                  value={nextDueDate}
                  onChange={(e) => { setNextDueDate(e.target.value); }}
                  autoComplete="off"
                  InputLabelProps={{ shrink: true }}
                  inputProps={{
                    min: currentDateValidator()
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
                <DialogActions sx={{ margin: '0px' }}>
                  <Button
                    disabled={inputDisable === true}
                    size="large"
                    type="submit"
                  >
                    {' '}
                    {isAddButton ? 'Submit' : 'Update'}
                  </Button>
                  <Button
                    size="large"
                    autoFocus
                    /* eslint-disable-next-line */
                    onClick={onCancel}
                  >
                    Cancel
                  </Button>

                </DialogActions>
              </Grid>
            </Grid>
          </div>
        </DialogContent>

      </form>
      <DialogContent>
        <div style={{
          height: 250,
          width: '100%',
          margin: '0px',
          '& .super-app.Pass': {
            backgroundColor: '#d47483',
            color: '#1a3e72',
            fontWeight: '600',
          },
        }}
        >
          <DataGrid
            rows={bumpTestData}
            columns={columns}
            pageSize={3}
            rowsPerPageOptions={[5]}
            disableSelectionOnClick
          />
        </div>
      </DialogContent>
      <NotificationBar
        hideLimit={3000}
        handleClose={handleClose}
        notificationContent={openNotification.message}
        openNotification={openNotification.status}
        type={openNotification.type}
      />
      <Dialog
        fullWidth
        maxWidth="md"
        sx={{ '& .MuiDialog-paper': { width: '90%', maxHeight: '100%', minWidth: '350px' } }}
        open={cylinderDialog}
        style={{
          width: '100%'
        }}
        // {props.open}
      >
        <DialogTitle sx={{ textAlign: 'center' }}>
          <WarningAmber color="warning" style={{ fontSize: 95 }} />
          <br />
          <Typography
            // sx={{ m: 1 }}
            variant="h5"
            component="span"
          >
            Warning!
          </Typography>
        </DialogTitle>
        <DialogContent sx={{ textAlign: 'center' }}>
          <Typography
            variant="h6"
            component="span"
          >
            Kindly check the expiry date of the Span Check / Zero Check gas cylinder. 
          </Typography>
          <br />
          Using expired cylinders may result in improper readings and improper results. Please proceed only if date is within the expiry date.
        </DialogContent>
        <DialogActions sx={{ margin: '10px' }}>
          <div style={{ textAlign: 'center' }}>
            <Button 
              onClick={() => {
                // setExpiryDate(true);
                setCylinderDialog(false);
                getBumpData();
              }}
            >
              Proceed
            </Button>
            <Button
              onClick={() => {
                // setExpiryDate(false);
                setCylinderDialog(false);
                onCancel();
              }}
            >
              Cancel
            </Button>
          </div>
        </DialogActions>
      </Dialog>
    </Dialog>
  );
}

export default BumpTestComponentModal;
