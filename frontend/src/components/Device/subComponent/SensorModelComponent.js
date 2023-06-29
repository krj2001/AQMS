import React, { useState, useEffect } from 'react';
import { styled } from '@mui/material/styles';
import { DataGrid } from '@mui/x-data-grid';
import SensorsIcon from '@mui/icons-material/Sensors';
import {
  Grid, DialogTitle, List, Box, Dialog, Button, DialogContent,
  ListItem, ListItemButton, ListItemAvatar, ListItemText, Avatar,
  FormControl, InputLabel, MenuItem, Select, TextField, Typography,
} from '@mui/material';
import {
  CalibrationAddService,
  ChangeDeviceMode,
  DeployedSensorsDetailsList,
  SensorDeployDeleteService,
  SensorPropertiesUpdateService,
} from '../../../services/LoginPageService';
import NotificationBar from '../../notification/ServiceNotificationBar';
import SensorAdd from '../SensorAdd';
import SensorSettingsButton from './SensorSettingsButton';
import SensorSettingsMenu from './SensorSettingsMenu';
import DeleteConfirmationDailog from '../../../utils/confirmDeletion';
import { useUserAccess } from '../../../context/UserAccessProvider';

const ListWrapper = styled('div')(({ theme }) => ({
  backgroundColor: theme.palette.background.paper,
}));

function SensorModel({
  open,
  setOpen,
  locationDetails,
  analogSensorList,
  digitalSensorList,
  modbusSensorList,
  setRefreshData,
  progressStatus,
  setProgressStatus,
  deployedSensorTagList,
  setSensorRefresh,
  device_id,
  calibratingDeviceId
}) {
  const moduleAccess = useUserAccess()('devicelocation');
  const [editData, setEditData] = useState('');
  const [sensorTag, setSensorTag] = useState('');
  const [name, setName] = useState('');
  const [partId, setPartId] = useState('');
  const [testResult, setTestResult] = useState('');
  const [nextDueDate, setNextDueDate] = useState('');
  const [calibratedDate, setCalibratedDate] = useState('');
  const [lastDueDate, setLastDueDate] = useState('');
  const [calibrationList, setCalibrationlist] = useState([]);

  const [openNotification, setNotification] = useState({
    status: false,
    type: 'error',
    message: '',
  });

  const [anchorEl, setAnchorEl] = useState(null);
  const [popperOpen, setPopperOpen] = useState(false);
  const [sensorUpdateId, setSensorUpdateId] = useState('');
  const [hooterRelayStatus, setHooterRelayStatus] = useState('0');
  const [sensorStatus, setSensorStatus] = useState('0');
  const [notificationStatus, setNotificationStatus] = useState('0');
  const [audioDecibelLevel, setAudioDecibelLevel] = useState('65');

  useEffect(() => {
  }, [analogSensorList || digitalSensorList || modbusSensorList]);

  const columns = [
    {
      field: 'calibrationDate',
      headerName: 'Date',
      minWidth: 110,
      flex: 1,
      align: 'center',
      headerAlign: 'center',
      editable: true,
      renderCell: (params) => (
        <Typography>
          {
            dateFormat(params.value)
          }
        </Typography>
      ),
    },
    // {
    //   headerName: 'Date',
    //   minWidth: 100,
    //   flex: 1,
    //   align: 'center',
    //   headerAlign: 'center',
    //   editable: true,
    //   renderCell: (params) => (
    //     <Typography>
    //       {
    //         timeFormat(params.row.calibrationDate)
    //       }
    //     </Typography>
    //   ),
    // },
    {
      field: 'name',
      headerName: 'Name',
      minWidth: 150,
      flex: 1,
      align: 'center',
      headerAlign: 'center',
      editable: true,
    },
    {
      field: 'model',
      headerName: 'Part Id',
      minWidth: 150,
      flex: 1,
      align: 'center',
      headerAlign: 'center',
      editable: true,
    },
    {
      field: 'testResult',
      headerName: 'Test Result',
      minWidth: 100,
      flex: 1,
      align: 'center',
      headerAlign: 'center',
      editable: true,
    },
    {
      field: 'lastDueDate',
      headerName: 'Calibration Due Date',
      minWidth: 200,
      flex: 1,
      align: 'center',
      headerAlign: 'center',
      editable: true,
      renderCell: (params) => (
        <Typography>
          {
            params.value !== '' ? dateFormat(params.value) : ''
          }
        </Typography>
      ),
    },
    {
      field: 'calibratedDate',
      headerName: 'Calibrated Date',
      minWidth: 150,
      flex: 1,
      align: 'center',
      headerAlign: 'center',
      editable: true,
      renderCell: (params) => (
        <Typography>
          {
            params.value !== '' ? dateFormat(params.value) : ''
          }
        </Typography>
      ),
    },
    {
      field: 'nextDueDate',
      headerName: 'Next Due Date',
      minWidth: 150,
      flex: 1,
      align: 'center',
      headerAlign: 'center',
      editable: true,
      renderCell: (params) => (
        <Typography>
          {
            dateFormat(params.value)
          }
        </Typography>
      ),
    },
  ];

  const [deleteDailogOpen, setDeleteDailogOpen] = useState(false);
  const [deleteId, setDeleteId] = useState('');

  const dateFormat = (value) => {
    var dateValue = '';
    if(value){
      const dateTime = value?.split(' ');
      const date = dateTime[0]?.split('-');
      dateValue = `${date[2]}-${date[1]}-${date[0]}`;
    }
    return dateValue || '';
  };

  const timeFormat = (value) => {
    const dateTime = value.split(' ');
    const dateValue = dateTime[1];
    return dateValue;
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    CalibrationAddService({
      device_id : calibratingDeviceId, sensorTag, name, model: partId, testResult, nextDueDate, calibratedDate, lastDueDate
    }, calibrationHandleSuccess, calibrationHandleException);
  };

  const calibrationHandleSuccess = (dataObject) => {
    setNotification({
      status: true,
      type: 'success',
      message: dataObject.message,
    });
    setRefreshData((oldvalue) => !oldvalue);
    setTimeout(() => {
      handleClose();
      setSensorTag('');
      setName('');
      setPartId('');
      setTestResult('');
      setNextDueDate('');
      setLastDueDate('');
      setCalibratedDate('');
      setCalibrationlist([]);
    }, 2000);
  };

  const calibrationHandleException = (dataObject, errorMessage) => {
    setNotification({
      status: true,
      type: 'error',
      message: errorMessage,
    });
    setTimeout(() => {
      handleClose();
    }, 3000);
  };

  const deleteSensor = (id) => {
    setDeleteId(id);
    setDeleteDailogOpen(true);
  };

  const handleSuccess = (dataObject) => {
    setNotification({
      status: true,
      type: 'success',
      message: dataObject.message,
    });

    setRefreshData((oldvalue) => !oldvalue);

    setTimeout(() => {
      handleClose();
      setDeleteDailogOpen(false);
      setOpen(false);
    }, 3000);
  };

  const handleException = (errorObject, errorMessage) => {
    setNotification({
      status: true,
      type: 'error',
      message: errorMessage,
    });
    setTimeout(() => {
      handleClose();
    }, 3000);
  };
  /* eslint-disable-next-line */
  const fetchCalibrationDetails = (sensorTag) => {
    var sensorObject = {};
    sensorObject = deployedSensorTagList?.find((item)=>{
      return item.sensorTag === sensorTag;
    });
    DeployedSensorsDetailsList({ sensorTag, id: sensorObject.id }, calibrationdetailsHandleSuccess, calibrationDetailsHandleException);
  };
  const calibrationdetailsHandleSuccess = (dataObject) => {
    setName(dataObject?.sensorNameUnit || '');
    setLastDueDate(dataObject?.lastDueDate || '');
    setPartId(dataObject?.partId || '')
    setCalibrationlist(dataObject.data || []);
  };

  const calibrationDetailsHandleException = () => {};

  const handleCancel = () => {
    ChangeDeviceMode({ id: calibratingDeviceId, deviceMode: 'enabled' }, cancelHandleSuccess, modeChangeHandleException);
  };
  
  const clearOnCancel = () =>{
    setRefreshData((oldvalue) => !oldvalue);
    setProgressStatus(0);
    setOpen(false);
    setSensorTag('');
    setName('');
    setPartId('');
    setTestResult('');
    setNextDueDate('');
    setLastDueDate('');
    setCalibratedDate('');
    setCalibrationlist([]);
  }
  const cancelHandleSuccess = () => {
    clearOnCancel();
  };

  const modeChangeHandleException = (errorObject, errorMessage) => {
    setNotification({
      status: true,
      type: 'error',
      message: errorMessage,
    });
    clearOnCancel();
  };

  const handleClose = () => {
    setNotification({
      status: false,
      type: '',
      message: '',
    });
  };

  const successSensorUpdate = () => {
    setSensorRefresh(oldvalue => !oldvalue);
  };

  const handleFailure = () => {};

  const updateSensorProperties = (id, sensorProperties) => {
    SensorPropertiesUpdateService({ ...id, ...sensorProperties }, successSensorUpdate, handleFailure);
  };

  const setSensorIdForOptions = (data) => {
    setPopperOpen(true);
    setSensorUpdateId(data.id);
    setSensorStatus(data.sensorStatus || '0');
    setNotificationStatus(data.notificationStatus || '0');
    setHooterRelayStatus(data.hooterRelayStatus || '0');
    setAudioDecibelLevel(data.audioDecibelLevel || '65');
  };

  return (
    <Dialog
      fullWidth
      maxWidth="lg"
      sx={{ '& .MuiDialog-paper': { width: '100%' } }}
      open={open}
    >
      {progressStatus === 1 && (
        <>
          <DialogTitle
            style={{
              fontFamily: 'customfont',
              letterSpacing: '1px',
              fontWeight: '600',
              textAlign: 'center',
              padding: '30px 0',
              fontSize: '22px'
            }}
          >
            Sensors for device
          </DialogTitle>
          <DialogContent style={{ padding: '10px' }}>
            <SensorSettingsMenu
              anchorEl={anchorEl}
              popperOpen={popperOpen}
              setPopperOpen={setPopperOpen}
              sensorProperties={{
                id: sensorUpdateId,
                notificationStatus_u: notificationStatus,
                sensorStatus: sensorStatus,
                hooterRelayStatus_u: hooterRelayStatus,
                audioDecibelLevel_u: audioDecibelLevel,
              }}
              deleteSensor={deleteSensor}
              updateService={updateSensorProperties}
              setHooterRelayStatus={setHooterRelayStatus}
              setSensorStatus={setSensorStatus}
              setAudioDecibelLevel={setAudioDecibelLevel}
              setNotificationStatus={setNotificationStatus}
            />
            <Box sx={{ flexGrow: 1, width: '100%', height: 300 }}>
              <Grid container spacing={2}>
                <Grid item xs={12} md={4}>
                  <div
                    style={{
                      fontFamily: 'customfont',
                      fontWeight: '600',
                      letterSpacing: '1px',
                      textAlign: 'center',
                      padding: '10px 0',
                      fontSize: '19px'
                    }}
                    >
                    Analog
                  </div>
                  <ListWrapper style={{ maxHeight: 300, overflow: 'auto' }}>
                    <List dense={false}>
                      {analogSensorList.length > 0
                        ? analogSensorList.map((data) => {
                          return (
                            <ListItem component="li" disablePadding>
                              <ListItemButton sx={{ height: 56 }}>
                                <Grid
                                  container
                                  style={{ display: 'contents' }}
                                  onClick={() => {
                                    setEditData(data);
                                    setProgressStatus(2);
                                  }
                                }
                                >
                                  <ListItemAvatar >
                                    <Avatar>
                                      <SensorsIcon />
                                    </Avatar>
                                  </ListItemAvatar>
                                  <ListItemText
                                    primary={data.sensorTag}
                                    secondary={data.sensorNameUnit}
                                  />
                                </Grid>
                                {
                                  moduleAccess.view === true
                                    ? (
                                      <SensorSettingsButton
                                        setAnchorEl={setAnchorEl}
                                        setPopperOpen={() => {
                                          setSensorIdForOptions(data);
                                        }}
                                        handleClose={handleClose}
                                      />
                                    )
                                    : <></>
                                }
                              </ListItemButton>
                            </ListItem>
                          );
                        })
                        : (
                          <ListItem
                            style={{
                              display: 'block',
                              textAlignLast: 'center',
                              fontFamily: 'customfont'
                            }}
                          >
                            <ListItemAvatar />
                            <span style={{ display: 'block', textAlignLast: 'center', fontFamily: 'customfont', letterSpacing: '1px', fontWeight: '600' }}>No Analog Sensors</span>
                          </ListItem>
                        )}
                    </List>
                  </ListWrapper>
                </Grid>
                <Grid item xs={12} md={4}>
                  <div style={{
                    fontFamily: 'customfont',
                    fontWeight: '600',
                    letterSpacing: '1px',
                    textAlign: 'center',
                    padding: '10px 0',
                    fontSize: '19px'
                  }}>
                    Modbus
                  </div>
                  <ListWrapper style={{ maxHeight: 300, overflow: 'auto' }}>
                    <List dense={false}>
                      {modbusSensorList.length > 0 ? (
                        modbusSensorList.map((data) => {
                          return (
                            <ListItem component="li" disablePadding>
                              <ListItemButton sx={{ height: 56 }}>
                                <Grid
                                  container
                                  style={{ display: 'contents' }}
                                  onClick={() => {
                                    setEditData(data);
                                    setProgressStatus(2);
                                  }}
                                >
                                  <ListItemAvatar>
                                    <Avatar>
                                      <SensorsIcon />
                                    </Avatar>
                                  </ListItemAvatar>
                                  <ListItemText
                                    primary={data.sensorTag}
                                    secondary={data.sensorNameUnit}
                                  />
                                </Grid>
                                {
                                  moduleAccess.view === true
                                    ? (
                                      <SensorSettingsButton
                                        setAnchorEl={setAnchorEl}
                                        setPopperOpen={() => setSensorIdForOptions(data)}
                                        handleClose={handleClose}
                                      />
                                    )
                                    : <></>
                                }
                              </ListItemButton>
                            </ListItem>
                          );
                        })
                      ) : (
                        <ListItem
                          style={{ display: 'block', textAlignLast: 'center' }}
                        >
                          <ListItemAvatar />
                          <span
                            style={{
                              display: 'block',
                              textAlignLast: 'center',
                              fontFamily: 'customfont',
                              fontWeight: '600',
                              letterSpacing: '1px'
                            }}
                          >
                            No Modbus Sensors
                          </span>
                        </ListItem>
                      )}
                    </List>
                  </ListWrapper>
                </Grid>
                <Grid item xs={12} md={4}>
                  <div
                    style={{
                      fontFamily: 'customfont',
                      fontWeight: '600',
                      letterSpacing: '1px',
                      textAlign: 'center',
                      padding: '10px 0',
                      fontSize: '19px'
                    }}>
                    Digital
                  </div>
                  <ListWrapper>
                    <List dense={false}>
                      {digitalSensorList.length > 0 ? (
                        digitalSensorList.map((data) => {
                          return (
                            <ListItem component="li" disablePadding sx={{ fontFamily: 'customfont' }}>
                              <ListItemButton sx={{ height: 56 }}>
                                <Grid
                                  container
                                  style={{ display: 'contents', fontFamily: 'customfont' }}
                                  onClick={() => {
                                    setEditData(data);
                                    setProgressStatus(2);
                                  }}
                                >
                                  <ListItemAvatar>
                                    <Avatar>
                                      <SensorsIcon />
                                    </Avatar>
                                  </ListItemAvatar>
                                  <ListItemText
                                    primary={data.sensorTag}
                                    secondary={data.sensorNameUnit}
                                  />
                                </Grid>
                                {
                                  moduleAccess.view === true
                                    ? (
                                      <SensorSettingsButton
                                        setAnchorEl={setAnchorEl}
                                        setPopperOpen={() => setSensorIdForOptions(data)}
                                        handleClose={handleClose}
                                      />
                                    )
                                    : ''
                                }
                              </ListItemButton>
                            </ListItem>
                          );
                        })
                      ) : (
                        <ListItem
                          style={{ display: 'block', textAlignLast: 'center', fontFamily: 'customfont' }}
                        >
                          <ListItemAvatar />
                          <span
                            style={{
                              display: 'block',
                              textAlignLast: 'center',
                              fontFamily: 'customfont',
                              fontWeight: '600',
                              letterSpacing: '1px'
                            }}
                          >
                            No Digital Sensors
                          </span>
                        </ListItem>
                      )}
                    </List>
                  </ListWrapper>
                </Grid>
              </Grid>
            </Box>
          </DialogContent>
          <Box>
            <div className="float-right">
              <div className="rounded-md -space-y-px">
                <Button
                  style={{
                    background: 'rgb(19 60 129)',}}
                  sx={{
                    m: 2,
                    height: '0',
                    color: 'white',
                    padding: "10px 19px",
                    fontSize: '13px',
                    borderRadius: '10px',
                    fontWeight: '600',
                    fontFamily: 'customfont',
                    letterSpacing: '1px',
                    boxShadow: 'none',
                    marginRight: '40px',
                    marginBottom: '30px'
                  }}
                  onClick={() => {
                    setOpen(false);
                  }}
                >
                  Cancel
                </Button>
              </div>
            </div>
          </Box>
        </>
      )}
      {progressStatus === 2 && (
        <div style={{ textAlign: 'center', padding: 5 }}>
          <SensorAdd isUpdate editData={editData} locationDetails={locationDetails} setProgressStatus={setProgressStatus} setSensorRefresh={setSensorRefresh} />
        </div>
      )}
      {progressStatus === 3 && (
        <Grid container spacing={1} sx={{ p: 3 }}>
          <Typography sx={{ m: 0, marginTop: 1 }} variant="h5">
            Calibration
          </Typography>
          <Grid container spacing={1}>
            <form onSubmit={handleSubmit}>
              <Grid container spacing={1}>
                <Grid container spacing={1} sx={{ mt: 3, ml: 2 }}>
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
                      <FormControl fullWidth margin="normal" sx={{ marginTop: 0 }}>
                        <InputLabel id="demo-simple-select-label">
                          Sensor Tag
                        </InputLabel>
                        <Select
                          // sx={{ minWidth: 250 }}
                          labelId="demo-simple-select-label"
                          id="demo-simple-select"
                          value={sensorTag}
                          required
                          // disabled={editData && true}
                          label="Sensor Tag"
                          onChange={(e) => {
                            setSensorTag(e.target.value);
                            fetchCalibrationDetails(e.target.value);
                          }}
                          // error={errorObject?.deviceCategory?.errorStatus}
                          // helperText={errorObject?.deviceCategory?.helperText}
                        >
                          {deployedSensorTagList.map((data) => {
                            return (
                              <MenuItem value={data.sensorTag}>{data.sensorTag}</MenuItem>
                            );
                          })}
                        </Select>
                      </FormControl>
                    </div>
                  </Grid> */}
                  <Grid
                    item
                    xs={12}
                    sm={6}
                    md={4}
                    lg={4}
                    xl={4}
                  >
                    <FormControl fullWidth margin="normal" sx={{ marginTop: 0 }}>
                      <InputLabel id="demo-simple-select-label">
                        Sensor Tag
                      </InputLabel>
                      <Select
                        // sx={{ minWidth: 250 }}
                        labelId="demo-simple-select-label"
                        id="demo-simple-select"
                        value={sensorTag}
                        required
                        // disabled={editData && true}
                        label="Sensor Tag"
                        onChange={(e) => {
                          setSensorTag(e.target.value);
                          fetchCalibrationDetails(e.target.value);
                        }}
                      // error={errorObject?.deviceCategory?.errorStatus}
                      // helperText={errorObject?.deviceCategory?.helperText}
                      >
                        {deployedSensorTagList.map((data) => {
                          return (
                            <MenuItem value={data.sensorTag}>{data.sensorTag}</MenuItem>
                          );
                        })}
                      </Select>
                    </FormControl>
                  </Grid>
                  <Grid
                    item
                    xs={12}
                    sm={6}
                    md={4}
                    lg={4}
                    xl={4}
                  >
                    <div className="rounded-md -space-y-px">
                      <TextField
                        sx={{ marginTop: 0 }}
                        value={name}
                        disabled
                        onChange={(e) => {
                          setName(e.target.value);
                        }}
                        margin="normal"
                        required
                        id="outlined-required"
                        label="Name"
                        fullWidth
                        autoComplete="off"
                      />
                    </div>
                  </Grid>
                  <Grid
                    item
                    xs={12}
                    sm={6}
                    md={4}
                    lg={4}
                    xl={4}
                  >
                    <div className="rounded-md -space-y-px">
                      <TextField
                        sx={{ marginTop: 0 }}
                        value={partId}
                        disabled
                        // onBlur={() => validateForNullValue(sensorTag, 'sensorTag')}
                        onChange={(e) => {
                          setPartId(e.target.value);
                        }}
                        margin="normal"
                        required
                        id="outlined-required"
                        label="Part Id"
                        fullWidth
                        // error={errorObject?.sensorTag?.errorStatus}
                        // helperText={errorObject?.sensorTag?.helperText}
                        autoComplete="off"
                      />
                    </div>
                  </Grid>
                  <Grid
                    item
                    xs={12}
                    sm={6}
                    md={3}
                    lg={3}
                    xl={3}
                  >
                    <div className="rounded-md -space-y-px">
                      <TextField
                        sx={{ marginTop: 0 }}
                        value={testResult}
                        // onBlur={() => validateForNullValue(sensorTag, 'sensorTag')}
                        onChange={(e) => {
                          setTestResult(e.target.value);
                        }}
                        margin="normal"
                        required
                        id="outlined-required"
                        label="Test Result"
                        fullWidth
                        // error={errorObject?.sensorTag?.errorStatus}
                        // helperText={errorObject?.sensorTag?.helperText}
                        autoComplete="off"
                      />
                    </div>
                  </Grid>
                  <Grid
                    item
                    xs={12}
                    sm={4}
                    md={3}
                    lg={3}
                    xl={3}
                  >
                    <div className="rounded-md -space-y-px">
                      <TextField
                        sx={{ marginTop: 0 }}
                        value={lastDueDate}
                        margin="normal"
                        disabled
                        id="outlined-required"
                        label="Calibration Due Date"
                        fullWidth
                        type="date"
                        autoComplete="off"
                        InputLabelProps={{ shrink: true }}
                      />
                    </div>
                  </Grid>
                  <Grid
                    item
                    xs={12}
                    sm={4}
                    md={3}
                    lg={3}
                    xl={3}
                  >
                    <div className="rounded-md -space-y-px">
                      <TextField
                        sx={{ marginTop: 0 }}
                        value={calibratedDate}
                        // onBlur={() => validateForNullValue(sensorTag, 'sensorTag')}
                        onChange={(e) => {
                          setCalibratedDate(e.target.value);
                        }}
                        margin="normal"
                        required
                        id="outlined-required"
                        label="Calibrated Date"
                        fullWidth
                        type="date"
                        // error={errorObject?.sensorTag?.errorStatus}
                        // helperText={errorObject?.sensorTag?.helperText}
                        autoComplete="off"
                        InputLabelProps={{ shrink: true }}
                      />
                    </div>
                  </Grid>
                  <Grid
                    item
                    xs={12}
                    sm={4}
                    md={3}
                    lg={3}
                    xl={3}
                  >
                    <div className="rounded-md -space-y-px">
                      <TextField
                        sx={{ marginTop: 0 }}
                        value={nextDueDate}
                        // onBlur={() => validateForNullValue(sensorTag, 'sensorTag')}
                        onChange={(e) => {
                          setNextDueDate(e.target.value);
                        }}
                        margin="normal"
                        required
                        id="outlined-required"
                        label="Next Calibration Date"
                        fullWidth
                        type="date"
                        // error={errorObject?.sensorTag?.errorStatus}
                        // helperText={errorObject?.sensorTag?.helperText}
                        autoComplete="off"
                        InputLabelProps={{ shrink: true }}
                      />
                    </div>
                  </Grid>
                </Grid>
                {/* Button Container */}
                <Grid
                  container
                  spacing={1}
                  xs={12}
                  sm={12}
                  md={12}
                  lg={12}
                  xl={12}
                  sx={{ marginTop: 1 }}
                >
                  <Grid
                    item
                    xs={12}
                    sm={12}
                    md={12}
                    lg={12}
                    xl={12}
                  >
                    <div className="float-right">
                      <Button type="submit"
                       style={{
                        background: 'rgb(19 60 129)',}}
                        sx={{
                          height: '0',
                          color: 'white',
                          padding: "10px 19px",
                          fontSize: '13px',
                          borderRadius: '10px',
                          fontWeight: '600',
                          fontFamily: 'customfont',
                          letterSpacing: '1px',
                          boxShadow: 'none',
                          marginRight: '20px'
                        }}>
                        Submit
                      </Button>
                      <Button
                        onClick={handleCancel}
                        style={{
                          background: 'rgb(19 60 129)',}}
                        sx={{
                          height: '0',
                          color: 'white',
                          padding: "10px 19px",
                          fontSize: '13px',
                          borderRadius: '10px',
                          fontWeight: '600',
                          fontFamily: 'customfont',
                          letterSpacing: '1px',
                          boxShadow: 'none',
                          marginRight: '20px'
                        }}
                      >
                        Cancel
                      </Button>
                    </div>
                  </Grid>
                </Grid>
              </Grid>
            </form>
          </Grid>
          <Grid container spacing={1} sx={{ ml: 0 }}>
            <Typography sx={{ m: 0, marginTop: 2 }} variant="h5">
              Calibration Details
            </Typography>
            <Grid
              sx={{ height: 250, width: '100%', paddingLeft: 0 }}
              item
              xs={12}
              sm={12}
              md={12}
              lg={12}
              xl={12}
            >
              <DataGrid
                rows={calibrationList}
                columns={columns}
                pageSize={5}
                // loading={isLoading}
                rowsPerPageOptions={[5]}
                disableSelectionOnClick
              />
            </Grid>
          </Grid>
        </Grid>
      )}
      <NotificationBar
        handleClose={handleClose}
        notificationContent={openNotification.message}
        openNotification={openNotification.status}
        type={openNotification.type}
      />
      <DeleteConfirmationDailog
        open={deleteDailogOpen}
        setOpen={setDeleteDailogOpen}
        deleteId={deleteId}
        deleteService={SensorDeployDeleteService}
        handleSuccess={handleSuccess}
        handleException={handleException}
      />
    </Dialog>
  );
}

export default SensorModel;
