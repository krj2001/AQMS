import React, { useState, useEffect } from 'react';
import {
  DialogContent, TextField, Box, Dialog, DialogTitle, Button, Select, FormControl, MenuItem, InputLabel, Grid,
} from '@mui/material';
import { DeviceAddService, DeviceEditService } from '../../../services/LoginPageService';
import DeviceAdd from '../DeviceAdd';
import NotificationBar from '../../notification/ServiceNotificationBar';
import DeviceLocationModal from '../deviceLocation/DeviceLocationModalComponent';
import { AddCategoryValidate } from '../../../validation/formValidation';
import { useUserAccess } from '../../../context/UserAccessProvider';

function DeviceModel({
  open,
  setOpen,
  isAddButton,
  deviceData,
  categoryData,
  locationDetails,
  labMap,
  setRefreshData,
}) {
  const moduleAccess = useUserAccess()('devicelocation');
  const [id, setId] = useState('');
  const [deviceName, setDeviceName] = useState('');
  const [deviceTag, setDeviceTag] = useState('');
  const [firmwareVersion, setFirmwareVersion] = useState('');
  const [hardwareModelVersion, setHardwareModelVersion] = useState('');
  const [macAddress, setMacAddress] = useState('');
  const [category_id, setCategory_id] = useState('');
  const [categoryList, setCategoryList] = useState([]);
  const [firmwareBinFile, setFirmwareBinFile] = useState({});
  const [binFileName, setBinFileName] = useState('');
  const [pollingPriority, setPollingPriority] = useState('');
  const [nonPollingPriority, setNonPollingPriority] = useState('');
  const [floorCords, setFloorCords] = useState('');
  const [errorObject, setErrorObject] = useState({});
  const [openModel, setOpenModel] = useState(false);
  const [openNotification, setNotification] = useState({
    status: false,
    type: 'error',
    message: '',
  });

  useEffect(() => {
    setOpen(open);
    loadData();
  }, [deviceData, categoryData]);

  const loadData = () => {
    setId(deviceData.id || '');
    setDeviceName(deviceData.deviceName || '');
    setDeviceTag(deviceData.deviceTag || '');
    setMacAddress(deviceData.macAddress || '');
    setFirmwareVersion(deviceData.firmwareVersion || '');
    setHardwareModelVersion(deviceData.hardwareModelVersion || '');
    setPollingPriority(deviceData.pollingPriority || '');
    setNonPollingPriority(deviceData.nonPollingPriority || '');
    setFloorCords(deviceData.floorCords || '');
    setCategoryList(categoryData || []);
    setCategory_id(deviceData.category_id || '');
    setBinFileName(deviceData.binFileName || '');
  };

  const validateForNullValue = (value, type) => {
    AddCategoryValidate(value, type, setErrorObject);
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
      setOpen(false);
    }, 5000);
  };

  const handleException = (errorObject, errorMessage) => {
    setNotification({
      status: true,
      type: 'error',
      message: errorMessage,
    });
  };

  const HandleSubmit = async (e) => {
    e.preventDefault();
    if (isAddButton) {
      await DeviceAddService(
        {
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
        },
        handleSuccess,
        handleException,
      );
    } else {
      await DeviceEditService(
        {
          id,
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
        },
        handleSuccess,
        handleException,
      );
    }
  };

  const resetForm = () => {
    setFirmwareBinFile({});
    setBinFileName(deviceData.binFileName || '');
  };

  const handleClose = () => {
    setNotification({
      status: false,
      type: '',
      message: '',
    });
  };
  return (
    <Dialog
      fullWidth
      maxWidth="md"
      sx={{ '& .MuiDialog-paper': { width: '95%', maxHeight: '95%' } }}
      open={open}
    >
      <DialogTitle
        sx={{ fontFamily: 'customfont', textAlign: 'center', fontWeight: '600', letterSpacing: '1px', padding: '30px 0' }}
      >{isAddButton ? 'Add Device' : 'Edit Device'}</DialogTitle>
      <DialogContent>
        <form onSubmit={HandleSubmit}>
          <div className='flex gap-4 min-[320px]:flex-col min-[768px]:flex-row'>
            <div className="rounded-md -space-y-px w-full">
              <Box sx={{ minWidth: 250 }}>
                <FormControl fullWidth margin="normal">
                  <InputLabel id="demo-simple-select-label" sx={{ fontFamily: 'customfont', fontSize: '15.2px' }}>
                    Device Category
                  </InputLabel>
                  <Select
                    sx={{ minWidth: 250 }}
                    labelId="demo-simple-select-label"
                    id="demo-simple-select"
                    value={category_id}
                    label="Device Category"
                    placeholder='Device Category'
                    // onBlur={() => { validateForNullValue(deviceCategory, 'deviceCategory')}}
                    onChange={(e) => {
                      setCategory_id(e.target.value);
                    }}
                    error={errorObject?.deviceCategory?.errorStatus}
                    helperText={errorObject?.deviceCategory?.helperText}

                    InputProps={{ style: { fontFamily: 'customfont' } }}

                  >
                    {categoryList.map((data) => (
                      <MenuItem value={data.id}>{data.categoryName}</MenuItem>
                    ))}
                  </Select>
                </FormControl>
              </Box>

            </div>
            <div className="rounded-md -space-y-px w-full">
              <TextField
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
                InputLabelProps={{
                  style: { fontFamily: 'customfont', fontSize: '15px' }
                }}
                InputProps={{ style: { fontFamily: 'customfont' } }}
              />
            </div>
            <div className="rounded-md -space-y-px w-full">
              <TextField
                value={deviceTag}
                onBlur={() => validateForNullValue(deviceTag, 'deviceTag')}
                onChange={(e) => {
                  setDeviceTag(e.target.value);
                  // setPreviewImage(e.target.value);
                }}
                margin="normal"
                autoComplete="off"
                required
                id="outlined-required"
                label="Device Tag"
                fullWidth
                error={errorObject?.deviceTag?.errorStatus}
                helperText={errorObject?.deviceTag?.helperText}
                InputLabelProps={{
                  style: { fontFamily: 'customfont', fontSize: '15px' }
                }}
                InputProps={{ style: { fontFamily: 'customfont' } }}
              />
            </div>
          </div>
          <div className='flex gap-4 min-[320px]:flex-col min-[768px]:flex-row'>
            <div className="rounded-md -space-y-px w-full">
              <TextField
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
                InputLabelProps={{
                  style: { fontFamily: 'customfont', fontSize: '15px' }
                }}
                InputProps={{ style: { fontFamily: 'customfont' } }}
              />
            </div>
            <div className="rounded-md -space-y-px w-full">
              <TextField
                value={firmwareVersion}
                onBlur={() => validateForNullValue(firmwareVersion, 'firmwareVersion')}
                onChange={(e) => {
                  setFirmwareVersion(e.target.value);
                }}
                margin="normal"
                id="outlined-required"
                label="Firmware Version"
                autoComplete="off"
                fullWidth
                error={errorObject?.firmwareVersion?.errorStatus}
                helperText={errorObject?.firmwareVersion?.helperText}
                InputLabelProps={{
                  style: { fontFamily: 'customfont', fontSize: '15px' }
                }}
                InputProps={{ style: { fontFamily: 'customfont' } }}
              />
            </div>
            <div className="rounded-md -space-y-px w-full mb-5 min-[320px]:mb-5 min-[768px]:mb-0">
              <TextField
                value={hardwareModelVersion}
                onBlur={() => validateForNullValue(hardwareModelVersion, 'hardwareModelVersion')}
                onChange={(e) => {
                  setHardwareModelVersion(e.target.value);
                }}
                margin="normal"
                id="outlined-required"
                label="Hardware Model No."
                autoComplete="off"
                fullWidth
                error={errorObject?.hardwareModelVersion?.errorStatus}
                helperText={errorObject?.hardwareModelVersion?.helperText}
                InputLabelProps={{
                  style: { fontFamily: 'customfont', fontSize: '15px' }
                }}
                InputProps={{ style: { fontFamily: 'customfont' } }}
              />
            </div>
          </div>
          <Grid

            sx={{ mt: 2, padding: 0, width: '100 %' }}
            container
            xs={12}
            sm={12}
            md={12}
            lg={12}
            xl={12}
          >
            <Grid
              sx={{ mt: 0, padding: 0, width: '100 %' }}
              item
              xs={12}
              sm={6}
              md={6}
              lg={6}
              xl={6}
            >
              <div className='rounded-md -space-y-px w-full mb-5 min-[320px]:mb-5 min-[768px]:mb-0 pr-5 min-[320px]:pr-0 min-[768px]:pr-5 '>
                <TextField
                  sx={{ marginTop: 0, paddingRight: 0, width: '100 %' }}
                  value={pollingPriority}
                  type="number"
                  placeholder="Enter value in Seconds"
                  onBlur={() => validateForNullValue(pollingPriority, 'pollingPriority')}
                  onChange={(e) => {
                    setPollingPriority(e.target.value);
                  }}
                  InputLabelProps={{
                    shrink: true,
                    style: { fontFamily: 'customfont', fontSize: '15px' }
                  }}
                  InputProps={{ style: { fontFamily: 'customfont' } }}
                  margin="normal"
                  required
                  id="outlined-required"
                  label="Polling Priority Value"
                  autoComplete="off"
                  fullWidth
                  error={errorObject?.pollingPriority?.errorStatus}
                  helperText={errorObject?.pollingPriority?.helperText}
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
              <div className='rounded-md -space-y-px w-full mb-5 min-[320px]:mb-5 min-[768px]:mb-0'>

                <TextField
                  sx={{ marginTop: 0, width: '100 %' }}
                  value={nonPollingPriority}
                  type="number"
                  placeholder="Enter value in Seconds"
                  onBlur={() => validateForNullValue(nonPollingPriority, 'nonPollingPriority')}
                  onChange={(e) => {
                    setNonPollingPriority(e.target.value);
                  }}
                  InputLabelProps={{
                    shrink: true,
                    style: { fontFamily: 'customfont', fontSize: '15px' }
                  }}
                  InputProps={{ style: { fontFamily: 'customfont' } }}
                  margin="normal"
                  required
                  id="outlined-required"
                  label="Polling Non-Priority Value"
                  autoComplete="off"
                  fullWidth
                  error={errorObject?.nonPollingPriority?.errorStatus}
                  helperText={errorObject?.nonPollingPriority?.helperText}
                />
              </div>
            </Grid>
          </Grid>
          <Grid
            sx={{ mt: 2, padding: 0 }}
            container
            xs={12}
            sm={12}
            md={12}
            lg={12}
            xl={12}
          >
            <Grid
              sx={{ mt: 0, padding: 0 }}
              item
              xs={12}
              sm={12}
              md={6}
              lg={6}
              xl={6}
            >
              <div className='rounded-md -space-y-px w-full mb-5 min-[320px]:mb-5 min-[768px]:mb-0 pr-5 min-[320px]:pr-0 min-[768px]:pr-5'>

                <TextField
                  sx={{ marginTop: 0, paddingRight: 0, width: '100 %' }}
                  value={binFileName}
                  disabled
                  type="text"
                  placeholder=""
                  label="Current Bin File"
                  InputLabelProps={{
                    shrink: true,
                    style: { fontFamily: 'customfont', fontSize: '15px' }
                  }}
                  InputProps={{ style: { fontFamily: 'customfont' } }}
                  margin="normal"
                  autoComplete="off"
                  fullWidth
                />
              </div>
            </Grid>
            <Grid
              sx={{ mt: 0, padding: 0 }}
              item
              xs={12}
              sm={12}
              md={6}
              lg={6}
              xl={6}
            >
              <div className='rounded-md -space-y-px w-full mb-5 min-[320px]:mb-5 min-[768px]:mb-0'>

                <TextField
                  sx={{ marginTop: 0, width: '100 %' }}
                  margin="normal"
                  fullWidth
                  label="Upload Bin file"
                  autoComplete="off"
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
                  InputLabelProps={{ shrink: true, style: { fontFamily: 'customfont', fontSize: '15px' } }}
                  InputProps={{ style: { fontFamily: 'customfont' } }}
                  type="file"
                  inputProps={{
                    accept: '.bin',
                  }}
                  error={errorObject?.deviceImage?.errorStatus}
                  helperText={errorObject?.deviceImage?.helperText}
                />
              </div>
            </Grid>


          </Grid>
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
                  marginRight: '20px'
                }}
                onClick={(e) => {
                  setOpen(false);
                  setErrorObject({});
                  loadData();
                  resetForm();
                }}
              >
                Cancel
              </Button>
              {moduleAccess.edit &&
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
                    marginRight: '20px',
                    "&.Mui-disabled": {
                      background: "#eaeaea",
                      color: "#c0c0c0"
                    }
                  }}
                  type="submit"
                  disabled={
                    errorObject?.deviceName?.errorStatus
                    || errorObject?.deviceTag?.errorStatus
                    || errorObject?.macAddress?.errorStatus
                    || errorObject?.firmwareVersion?.errorStatus
                    || errorObject?.hardwareModelVersion?.errorStatus
                    || errorObject?.pollingPriority?.errorStatus
                    || errorObject?.nonPollingPriority?.errorStatus
                  }
                >
                  {isAddButton ? 'Add' : 'Update'}
                </Button>
              }
            </div>
          </div>
        </form>
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
              onClick={(e) => {
                setOpenModel(true);
              }}
            >
              Locate Device
            </Button>
          </div>
        </Grid>
        {/* <DeviceAdd locationDetails={locationDetails} labMap={labMap} deviceData={deviceData} setOpen={setOpen}/> */}
      </DialogContent>
      <NotificationBar
        handleClose={handleClose}
        notificationContent={openNotification.message}
        openNotification={openNotification.status}
        type={openNotification.type}
      />
      <DeviceLocationModal
        open={openModel}
        setOpen={setOpenModel}
        src={labMap}
        floorCords={floorCords}
        setFloorCords={setFloorCords}
      />
    </Dialog>
  );
}

export default DeviceModel;
