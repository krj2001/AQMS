import React, { useState, useEffect } from 'react';
import {
  Button,
  DialogContent,
  IconButton,
  InputAdornment,
  InputLabel, Select,
  Typography,
  FormControl,
  TextField,
  MenuItem,
  Grid,
  FormLabel,
  FormControlLabel,
  Radio,
  RadioGroup,

} from '@mui/material';
import { styled } from '@mui/material/styles';
import Paper from '@mui/material/Paper';

import { Box } from '@mui/system';

import { CategoryFetchService, DeviceAddService } from '../../services/LoginPageService';
import DeviceLocationModal from './deviceLocation/DeviceLocationModalComponent';
import NotificationBar from '../notification/ServiceNotificationBar';
import { AddCategoryValidate } from '../../validation/formValidation';

const Item = styled(Paper)(({ theme }) => ({
  backgroundColor: theme.palette.mode === 'dark' ? '#1A2027' : '#fff',
  ...theme.typography.body2,
  padding: theme.spacing(1),
  textAlign: 'center',
  color: theme.palette.text.secondary,
}));

function DeviceAdd({ locationDetails, labMap, deviceData }) {
  const [id, setId] = useState('');
  const [deviceName, setDeviceName] = useState('');
  const [deviceCategory, setDeviceCategory] = useState('');
  const [firmwareBinFile, setFirmwareBinFile] = useState({});
  const [binFileName, setBinFileName] = useState('');
  const [deviceTag, setDeviceTag] = useState('');
  const [macAddress, setMacAddress] = useState('');
  const [firmwareVersion, setFirmwareVersion] = useState('');
  const [hardwareModelVersion, setHardwareModelVersion] = useState('');
  const [pollingPriority, setPollingPriority] = useState('');
  const [nonPollingPriority, setNonPollingPriority] = useState('');
  const [floorCords, setFloorCords] = useState('');
  const [category_id, setCategory_id] = useState('');
  const [categoryList, setCategoryList] = useState([]);
  const [openModel, setOpenModel] = useState(false);
  const [errorObject, setErrorObject] = useState({});
  const [hooter,setHooter] = useState('Non');
  const [openNotification, setNotification] = useState({
    status: false,
    type: 'error',
    message: '',
  });

  const validateForNullValue = (value, type) => {
    AddCategoryValidate(value, type, setErrorObject);
  };

  const handleSuccess = (dataObject) => {
    setNotification({
      status: true,
      type: 'success',
      message: dataObject.message,
    });

    setTimeout(() => {
      handleClose();
      resetForm();
    }, 5000);
  };

  const handleException = (errorObject, errorMessage) => {
    setNotification({
      status: true,
      type: 'error',
      message: errorMessage,
    });
  };

  useEffect(() => {
    loadCategory();
  }, [deviceData]);

  const categoryHandleSuccess = (dataObject) => {
    setCategoryList(dataObject.data);
  };

  const loadCategory = () => {
    CategoryFetchService(categoryHandleSuccess, handleException);
  };

  const handleSubmit = async (event) => {
    event.preventDefault();
    await DeviceAddService({
      deviceName,
      category_id,
      firmwareBinFile,
      binFileName,
      deviceTag,
      firmwareVersion,
      hardwareModelVersion,
      macAddress,
      pollingPriority,
      nonPollingPriority,
      floorCords,
      ...locationDetails,
    }, handleSuccess, handleException);
  };

  const resetForm = () => {
    setFirmwareVersion('');
    setHardwareModelVersion('');
    setPollingPriority('');
    setNonPollingPriority('');
    setDeviceName('');
    setMacAddress('');
    setDeviceTag('');
    setFirmwareBinFile({});
    setBinFileName('');
    setFloorCords('');
    setCategory_id('');
  };

  const handleClose = () => {
    setNotification({
      status: false,
      type: '',
      message: '',
    });
  };
  return (
    <>
      <form className=" w-full h-auto overflow-auto " onSubmit={handleSubmit}>
        <DialogContent sx={{ px: 0, p: 0 }} >
          <Grid
            container
            spacing={2}
          >
            <Grid
              sx={{ mt: 1 }}
              item
              xs={12}
              sm={6}
              md={6}
              lg={6}
              xl={6}
            >
              <Box sx={{ minWidth: 200 }}>
                <FormControl fullWidth margin="normal" sx={{ marginTop: 0 }}>
                  <InputLabel id="demo-simple-select-label" >
                    Device Category
                  </InputLabel>
                  <Select
                    sx={{ minWidth: 200, fontFamily: 'customfont' }}
                    labelId="demo-simple-select-label"
                    id="demo-simple-select"
                    value={category_id}
                    required
                    label="Device Category"
                    onChange={(e) => {
                      setCategory_id(e.target.value);
                    }}
                    error={errorObject?.deviceCategory?.errorStatus}
                    helperText={errorObject?.deviceCategory?.helperText}
                  >
                    {categoryList.map((data) => (
                      <MenuItem value={data.id} sx={{ fontFamily: 'customfont' }}>{data.categoryName}</MenuItem>
                    ))}
                  </Select>
                </FormControl>
              </Box>
            </Grid>
            <Grid
              sx={{ mt: 1, padding: 0 }}
              item
              xs={12}
              sm={6}
              md={6}
              lg={6}
              xl={6}
            >
              <TextField
                sx={{ marginTop: 0, fontFamily: 'customfont' }}
                value={deviceName}
                onBlur={() => validateForNullValue(deviceName, 'deviceName')}
                onChange={(e) => {
                  setDeviceName(e.target.value);
                }}
                margin="normal"
                required
                id="outlined-required"
                label="Name of the device"
                fullWidth
                error={errorObject?.deviceName?.errorStatus}
                helperText={errorObject?.deviceName?.helperText}
                autoComplete="off"
                InputProps={{
                  style: {
                    fontFamily: 'customfont'
                  }
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
                sx={{ marginTop: 0, fontFamily: 'customfont' }}
                value={deviceTag}
                onBlur={() => validateForNullValue(deviceTag, 'deviceTag')}
                onChange={(e) => {
                  setDeviceTag(e.target.value);
                }}
                margin="normal"
                autoComplete="off"
                required
                id="outlined-required"
                label="Device Tag"
                fullWidth
                error={errorObject?.deviceTag?.errorStatus}
                helperText={errorObject?.deviceTag?.helperText}
                InputProps={{
                  style: {
                    fontFamily: 'customfont'
                  }
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
                sx={{ marginTop: 0, fontFamily: 'customfont' }}
                value={macAddress}
                onBlur={() => validateForNullValue(macAddress, 'macAddress')}
                onChange={(e) => {
                  setMacAddress(e.target.value);
                }}
                margin="normal"
                required
                id="outlined-required"
                label="Mac Address"
                autoComplete="off"
                fullWidth
                error={errorObject?.macAddress?.errorStatus}
                helperText={errorObject?.macAddress?.helperText}
                InputProps={{
                  style: {
                    fontFamily: 'customfont'
                  }
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
                value={firmwareVersion}
                onBlur={() => validateForNullValue(firmwareVersion, 'firmwareVersion')}
                onChange={(e) => {
                  setFirmwareVersion(e.target.value);
                }}
                margin="normal"
                required
                id="outlined-required"
                label="Firmware version"
                autoComplete="off"
                fullWidth
                error={errorObject?.firmwareVersion?.errorStatus}
                helperText={errorObject?.firmwareVersion?.helperText}
                InputProps={{
                  style: {
                    fontFamily: 'customfont'
                  }
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
                value={hardwareModelVersion}
                onBlur={() => validateForNullValue(hardwareModelVersion, 'hardwareModelVersion')}
                onChange={(e) => {
                  setHardwareModelVersion(e.target.value);
                }}
                margin="normal"
                required
                id="outlined-required"
                label="Hardware version"
                autoComplete="off"
                fullWidth
                error={errorObject?.hardwareModelVersion?.errorStatus}
                helperText={errorObject?.hardwareModelVersion?.helperText}
                InputProps={{
                  style: {
                    fontFamily: 'customfont'
                  }
                }}
              />
            </Grid>
            <Grid
              sx={{ mt: 0, padding: 0 }}
              item
              xs={12}
              sm={12}
              md={12}
              lg={12}
              xl={12}
            >
              <TextField
                sx={{ marginTop: 0 }}
                margin="normal"
                fullWidth
                label="Upload Bin File (Max file size 1MB to be uploaded)"
                autoComplete="off"
                required
                onBlur={() => { validateForNullValue(firmwareBinFile, 'deviceImage'); }}
                onChange={(e) => {
                  if (e.target.files && e.target.files.length > 0) {
                    var fullPath = e.target.value;
                    if (fullPath) {
                      var startIndex = (fullPath.indexOf('\\') >= 0 ? fullPath.lastIndexOf('\\') : fullPath.lastIndexOf('/'));
                      var filename = fullPath.substring(startIndex);
                      if (filename.indexOf('\\') === 0 || filename.indexOf('/') === 0) {
                        filename = filename.substring(1);
                      }
                      setBinFileName(filename);
                    }
                    setFirmwareBinFile(e.target.files[0]);

                    const reader = new FileReader();
                    reader.onload = () => {
                      if (reader.readyState === 2) {
                        setFirmwareBinFile(reader.result);
                      }
                    };
                    reader.readAsDataURL(e.target.files[0]);
                  }
                }}
                InputLabelProps={{ shrink: true, style: { fontFamily: 'customfont' } }}
                type="file"
                inputProps={{
                  accept: '.bin',
                  style: { fontFamily: 'customfont' }
                }}
                error={errorObject?.deviceImage?.errorStatus}
                helperText={errorObject?.deviceImage?.helperText}
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
                value={pollingPriority}
                type="number"
                placeholder="Enter value in Seconds"
                onBlur={() => validateForNullValue(pollingPriority, 'pollingPriority')}
                onChange={(e) => {
                  setPollingPriority(e.target.value);
                }}
                InputProps={{
                  style: {
                    fontFamily: 'customfont'
                  }
                }}
                margin="normal"
                required
                id="outlined-required"
                label="Polling Priority Value"
                autoComplete="off"
                fullWidth
                error={errorObject?.pollingPriority?.errorStatus}
                helperText={errorObject?.pollingPriority?.helperText}
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
                value={nonPollingPriority}
                type="number"
                placeholder="Enter value in Seconds"
                onBlur={() => validateForNullValue(nonPollingPriority, 'nonPollingPriority')}
                onChange={(e) => {
                  setNonPollingPriority(e.target.value);
                }}
                InputProps={{
                  style: {
                    fontFamily: 'customfont'
                  }
                }}
                margin="normal"
                required
                id="outlined-required"
                label="Polling Non-Priority Value"
                autoComplete="off"
                fullWidth
                error={errorObject?.nonPollingPriority?.errorStatus}
                helperText={errorObject?.nonPollingPriority?.helperText}
              />
            </Grid>
            <Grid container justify="flex-end">
              <div className="float-right" />
            </Grid>
          </Grid>
          <Grid
            sx={{ mt: 0, padding: 0 }}
            item
            xs={12}
            sm={12}
            md={12}
            lg={12}
            xl={12}
            style={{ textAlignLast: 'right' }}
          >
            <div>
              <Button
                style={{
                  background: 'rgb(19 60 129)',}}
                sx={{
                  m: 2,
                  height: '40px',
                  color: 'white',
                  padding: '8px 19px',
                  marginTop: '10px',
                  marginRight: '10px',
                  // marginBottom: '35px',
                  bordeRadius: '10px',
                  fontWeight: '600',
                  fontFamily: 'customfont',
                  letterSpacing: '1px'
                }}
                onClick={(e) => {
                  setOpenModel(true);
                }}
              >
                Locate Device
              </Button>
            </div>
          </Grid>
          <Grid
            sx={{ mt: 0, padding: 0 }}
            item
            xs={12}
            sm={12}
            md={12}
            lg={12}
            xl={12}
          >
            <div className="mt-0 ml-2 float-right">
              <Button
                style={{
                  background: 'rgb(19 60 129)',}}
                sx={{
                  m: 1,
                  height: '40px',
                  color: 'white',
                  padding: '8px 19px',
                  // marginTop: '10px',
                  marginRight: '10px',
                  // marginBottom: '35px',
                  bordeRadius: '10px',
                  fontWeight: '600',
                  fontFamily: 'customfont',
                  letterSpacing: '1px'
                }}
                size="large"
                onClick={(e) => {
                  setErrorObject({});
                  resetForm();
                }}
              >
                Cancel
              </Button>
              <Button
                style={{
                  background: 'rgb(19 60 129)',}}
                sx={{
                  m: 1,
                  height: '40px',
                  color: 'white',
                  padding: '8px 19px',
                  background: 'rgb(19 60 129)',
                  // marginTop: '10px',
                  marginRight: '10px',
                  // marginBottom: '35px',
                  bordeRadius: '10px',
                  fontWeight: '600',
                  fontFamily: 'customfont',
                  letterSpacing: '1px',
                  "&.Mui-disabled": {
                    background: "#eaeaea",
                    color: "#c0c0c0"
                  }
                }}
                disabled={
                  errorObject?.deviceCategory?.errorStatus
                  || errorObject?.deviceName?.errorStatus
                  || errorObject?.deviceTag?.errorStatus
                  || errorObject?.macAddress?.errorStatus
                  || errorObject?.firmwareVersion?.errorStatus
                  || errorObject?.hardwareModelVersion?.errorStatus
                  || errorObject?.pollingPriority?.errorStatus
                  || errorObject?.nonPollingPriority?.errorStatus
                }

                size="large"
                type="submit"
              >
                ADD
              </Button>
            </div>
          </Grid>
        </DialogContent>

      </form>
      <DeviceLocationModal
        // isAddButton={isAddButton}
        // locationData={editState}
        // categoryList={categoryList}
        open={openModel}
        setOpen={setOpenModel}
        src={labMap}
        floorCords={floorCords}
        setFloorCords={setFloorCords}
      />
      <NotificationBar
        handleClose={handleClose}
        notificationContent={openNotification.message}
        openNotification={openNotification.status}
        type={openNotification.type}
      />
    </>
  );
}

export default DeviceAdd;
