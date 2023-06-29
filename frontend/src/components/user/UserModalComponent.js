import {
  Backdrop,
  /* eslint-disable max-len */
  Button, CircularProgress, Dialog, DialogContent, DialogTitle, FormControl, FormControlLabel, FormGroup, Grid, InputLabel, MenuItem, Select, Switch, TextField,
} from '@mui/material';
import React, { useEffect, useState } from 'react';
import {
  BuildingFetchService,
  FetchBranchService, FetchFacilitiyService, FetchLocationService, FloorfetchService, LabfetchService, UnblockUserService, UserAddService, UserUpdateService,
} from '../../services/LoginPageService';
import ApplicationStore from '../../utils/localStorageUtil';
import { AddUserValidate } from '../../validation/formValidation';
import NotificationBar from '../notification/ServiceNotificationBar';
import ConfirmPassword from './passwordConfirmComponent';

/* eslint-disable no-shadow */
/* eslint-disable react/no-array-index-key */
/* eslint-disable no-unused-vars */
function UserModal({
  open, setOpen, isAddButton, userData, setRefreshData,
}) {
  const { userDetails } = ApplicationStore().getStorage('userDetails');
  const isSuperAdmin = userDetails ? userDetails.userRole === 'superAdmin' : true;
  const { userRole } = userDetails;
  const [id, setId] = useState('');
  const [empId, setEmployeeId] = useState('');
  const [email, setEmailId] = useState('');
  const [phoneNo, setPhone] = useState('');
  const [empRole, setRole] = useState(isSuperAdmin ? 'superAdmin' : 'User');
  const [empName, setFullName] = useState('');
  const [empNotification, setEmpNotification] = useState(false);
  const [companyCode, setCompanyCode] = useState('');
  const [location_id, setLocationId] = useState('');
  const [branch_id, setBranchId] = useState('');
  const [facility_id, setFacilityId] = useState('');
  const [building_id, setBuildingId] = useState('');
  const [floor_id, setFloorId] = useState('');
  const [lab_id, setLabId] = useState('');
  const [locationList, setLocationList] = useState([]);
  const [branchList, setBranchList] = useState([]);
  const [facilityList, setFacilityList] = useState([]);
  const [buildingList, setBuildingList] = useState([]);
  const [floorList, setFloorList] = useState([]);
  const [labList, setLabList] = useState([]);
  const [password, setConfirmPassword] = useState('');
  const [btnReset, setBtnReset] = useState(false);
  const [errorObject, setErrorObject] = useState({});
  const [backdrop, setBackdrop] = useState(false);
  const [openNotification, setNotification] = useState({
    status: false,
    type: 'error',
    message: '',
  });

  useEffect(() => {
    if (userData) {
      setOpen(open);
      setBackdrop(true);
      loaddata();
    }
    if (isAddButton) {
      setBackdrop(false);
    }
  }, [userData, isAddButton]);

  const loaddata = () => {
    setBranchList([]);
    setFacilityList([]);
    setBuildingList([]);
    setFloorList([]);
    setLabList([]);
    setBranchId('');
    setFacilityId('');
    setBuildingId('');
    setFloorId('');
    setLabId('');
    if (!isAddButton) {
      if (userData?.location_id) {
        setBackdrop(true);
        FetchLocationService((locationRespObj) => {
          locationHandleSuccess(locationRespObj);
          FetchBranchService({
            location_id: userData?.location_id,
          }, (branchRespObj) => {
            setLocationId(userData.location_id);
            branchHandleSuccess(branchRespObj);
            if (userData?.branch_id) {
              FetchFacilitiyService({
                location_id: userData?.location_id,
                branch_id: userData?.branch_id,
              }, (facilityRespObj) => {
                setBranchId(userData.branch_id);
                facilityHandleSuccess(facilityRespObj);
                if (userData?.facility_id) {
                  BuildingFetchService({
                    location_id: userData?.location_id,
                    branch_id: userData?.branch_id,
                    facility_id: userData?.facility_id,
                  },(buildingRespObj)=>{
                    setFacilityId(userData.facility_id);
                    buildingHandleSuccess(buildingRespObj);
                    if(userData?.building_id){
                      FloorfetchService({
                        location_id: userData?.location_id,
                        branch_id: userData?.branch_id,
                        facility_id: userData?.facility_id,
                        building_id: userData?.building_id,
                      },(floorRespObj)=>{
                        setBuildingId(userData.building_id);
                        floorHandleSuccess(floorRespObj);
                        if(userData?.floor_id){
                          LabfetchService({
                            location_id: userData?.location_id,
                            branch_id: userData?.branch_id,
                            facility_id: userData?.facility_id,
                            building_id: userData?.building_id,
                            floor_id: userData?.floor_id,
                          },(labRespObj)=>{
                            labHandleSuccess(labRespObj);
                            if(userData?.lab_id){
                              setLabId(userData.lab_id);
                              setBackdrop(false);
                            }
                          },locationHandleException);
                          setFloorId(userData.floor_id);
                        }
                        else{
                          setBackdrop(false);
                        }
                      }, locationHandleException);
                      setBuildingId(userData.building_id);
                    }
                    else{
                      setBackdrop(false);
                    }
                  },locationHandleException);
                }
                else{
                  setBackdrop(false);
                }
              }, locationHandleException);
            } else {
              setBackdrop(false);
            }
          }, locationHandleException);
        }, locationHandleException);
      } else {
        setBackdrop(false);
      }
    } else {
      FetchLocationService((locationRespObj) => {
        locationHandleSuccess(locationRespObj);
        setBranchList([]);
        setFacilityList([]);
        setBuildingList([]);
        setFloorList([]);
        setLabList([]);
        setBranchId('');
        setFacilityId('');
        setBuildingId('');
        setFloorId('');
        setLabId('');
      }, locationHandleException);
    }
    setId(userData?.id || '');
    setEmployeeId(userData?.employeeId || '');
    setEmailId(userData?.email || '');
    setPhone(userData?.mobileno || '');
    if (isSuperAdmin) {
      setRole(userData?.user_role || 'superAdmin');
    } else {
      setRole(userData?.user_role || 'User');
    }
    setEmpNotification(userData?.empNotification === 1 ? true : false);
    setFullName(userData?.name || '');
    setCompanyCode(userData?.companyCode || '');
    setLocationId(userData?.location_id || '');
    setBranchId(userData?.branch_id || '');
    setFacilityId(userData?.facility_id || '');
    setBuildingId(userData?.building_id || '');
    setFloorId(userData?.floor_id || '');
    setLabId(userData?.lab_id || '');
  };

  const validateForNullValue = (value, type) => {
    AddUserValidate(value, type, setErrorObject);
  };

  const handleSuccess = (resErrorObject) => {
    setNotification({
      status: true,
      type: 'success',
      message: resErrorObject.message,
    });
    setRefreshData((oldvalue) => !oldvalue);
    setTimeout(() => {
      handleClose();
      setOpen(false);
      setErrorObject({});
    }, 3000);
  };

  const handleException = (resErrorObject, errorMessage) => {
    setNotification({
      status: true,
      type: 'error',
      message: errorMessage,
    });
  };

  const handleSubmit = async (event) => {
    event.preventDefault();
    if (isAddButton) {
      await UserAddService({
        location_id, branch_id, facility_id, building_id, floor_id, lab_id, empId, email, phoneNo, empRole, empName, empNotification,
      }, handleSuccess, handleException);
    } else {
      await UserUpdateService({
        location_id, branch_id, facility_id, building_id, floor_id, lab_id, empId, email, phoneNo, empRole, empName, empNotification, id,
      }, handleSuccess, handleException);
    }
  };

  const passwordSubmit = async (e) => {
    e.preventDefault();
    UnblockUserService(
      { email, password, id },
      passwordValidationSuccess,
      passwordValidationException,
    );
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

  const passwordValidationException = (resErrorObject, errorMessage) => {
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

  const locationHandleSuccess = (dataObject) => {
    setLocationList(dataObject.data);
    setBranchList([]);
    setFacilityList([]);
    setBuildingList([]);
    setFloorList([]);
    setLabList([]);
  };

  const locationHandleException = () => {};

  const branchHandleSuccess = (dataObject) => {
    setBranchList(dataObject.data);
    setFacilityList([]);
    setBuildingList([]);
    setFloorList([]);
    setLabList([]);
    setBranchId('');
    setFacilityId('');
    setBuildingId('');
    setFloorId('');
    setLabId('');
  };

  const branchHandleException = () => {};

  const facilityHandleSuccess = (dataObject) => {
    setFacilityList(dataObject.data);
    setBuildingList([]);
    setFloorList([]);
    setLabList([]);
    setFacilityId('');
    setBuildingId('');
    setFloorId('');
    setLabId('');
  };

  const facilityHandleException = () => {};

  const buildingHandleSuccess = (dataObject) =>{
    setBuildingList(dataObject.data);
    setFloorList([]);
    setLabList([]);
    setBuildingId('');
    setFloorId('');
    setLabId('');
  }

  const floorHandleSuccess = (dataObject) =>{
    setFloorList(dataObject.data);
    setLabList([]);
    setFloorId('');
    setLabId('');
  }

  const labHandleSuccess = (dataObject) =>{
    setLabList(dataObject.data);
    setLabId('');
    setBackdrop(false);
  }
  
  const onLocationChange = (location_id) => {
    setLocationId(location_id);
    if (location_id) {
      FetchBranchService({ location_id }, branchHandleSuccess, branchHandleException);
    } else {
      setBranchList([]);
      setFacilityList([]);
      setBuildingList([]);
      setFloorList([]);
      setLabList([]);
      setBranchId('');
      setFacilityId('');
      setBuildingId('');
      setFloorId('');
      setLabId('');
    }
  };

  const onBranchChange = (branch_id) => {
    setBranchId(branch_id);
    if (branch_id) {
      FetchFacilitiyService({ location_id, branch_id }, facilityHandleSuccess, facilityHandleException);
    } else {
      setFacilityList([]);
      setBuildingList([]);
      setFloorList([]);
      setLabList([]);
      setFacilityId('');
      setBuildingId('');
      setFloorId('');
      setLabId('');
    }
  };

  const onFacilityChange = (facility_id) => {
    setFacilityId(facility_id);
    if (facility_id) {
      BuildingFetchService({ location_id, branch_id, facility_id }, buildingHandleSuccess, locationHandleException);
    } else {
      setBuildingList([]);
      setFloorList([]);
      setLabList([]);
      setBuildingId('');
      setFloorId('');
      setLabId('');
    }
  };

  const onBuildingChange = (building_id) =>{
    setBuildingId(building_id);
    if (facility_id) {
      FloorfetchService({ location_id, branch_id, facility_id, building_id }, floorHandleSuccess, locationHandleException);
    } else {
      setFloorList([]);
      setLabList([]);
      setFloorId('');
      setLabId('');
    }
  }
  const onFloorChange = (floor_id) =>{
    setFloorId(floor_id);
    if (floor_id) {
      LabfetchService({ location_id, branch_id, facility_id, building_id, floor_id }, labHandleSuccess, locationHandleException);
    } else {
      setLabList([]);
      setLabId('');
    }
  }
  
  const onLabChange = (lab_id) =>{
    setLabId(lab_id);
  }


  return (
    <Dialog
      sx={{ '& .MuiDialog-paper': { minWidth: '80%' } }}
      maxWidth="sm"
      open={open}
    >
      <DialogTitle sx={{ fontFamily: 'customfont', fontWeight: '600', letterSpacing: '1px', textAlign: 'center' }}>
        {isAddButton ? 'Add User' : 'Edit User'}
      </DialogTitle>
      <DialogContent>
        <form className={'space-y-6'} onSubmit={handleSubmit}>
          <div className={'rounded-md  -space-y-px'}>
            {isSuperAdmin ? ''
              : (
                <Grid container spacing={2} sx={{ mt: 0, mb: 2 }}>
                  <Grid sx={{ mt: 0, padding: 0 }} item xs={12} sm={12} md={4} lg={4} xl={4}>
                    <div className="rounded-md -space-y-px">
                      <FormControl fullWidth>
                        {/* <InputLabel id="demo-simple-select-standard-label">Location</InputLabel> */}
                        <label className='text-left font-[customfont] font-medium mb-2 tracking-[1px] text-black'>Location</label>
                        <Select
                          className='text-left px-2 py-1 bg-[#f9f9f9] mr-4' sx={{ fontFamily: 'customfont', fontSize: '15px' }}
                          size='small'
                          displayEmpty
                          value={location_id}
                          onChange={(e) => onLocationChange(e.target.value)}
                        // label="Location"
                        >
                          <MenuItem value="" key={0}>
                            <em>N/A</em>
                          </MenuItem>
                          {locationList?.map((data, index) => {
                            return (
                              <MenuItem value={data.id} key={index + 1} sx={{ fontFamily: 'customfont' }}>{data.stateName}</MenuItem>
                            );
                          })}
                        </Select>
                      </FormControl>
                    </div>
                  </Grid>
                  <Grid sx={{ mt: 0, padding: 0 }} item xs={12} sm={12} md={4} lg={4} xl={4}>
                    <div className="rounded-md -space-y-px">
                      <FormControl fullWidth>
                        {/* <InputLabel id="demo-simple-select-standard-label">Branch</InputLabel> */}
                        <label className='text-left font-[customfont] font-medium mb-2 tracking-[1px] text-black'>Branch</label>
                        <Select
                          className='text-left px-2 py-1 bg-[#f9f9f9] mr-4' sx={{ fontFamily: 'customfont', fontSize: '15px' }}
                          size='small'
                          displayEmpty
                          value={branch_id}
                          onChange={(e) => onBranchChange(e.target.value)}
                        // label="Branch"
                        >
                          <MenuItem value="" key={0}>
                            <em>N/A</em>
                          </MenuItem>
                          {branchList?.map((data, index) => {
                            return (
                              <MenuItem value={data.id} key={index + 1}>{data.branchName}</MenuItem>
                            );
                          })}
                        </Select>
                      </FormControl>
                    </div>
                  </Grid>
                  <Grid sx={{ mt: 0, padding: 0 }} item xs={12} sm={12} md={4} lg={4} xl={4}>
                    <div className="rounded-md -space-y-px">
                      <FormControl fullWidth>
                        {/* <InputLabel id="demo-simple-select-standard-label">Facility</InputLabel> */}
                        <label className='text-left font-[customfont] font-medium mb-2 tracking-[1px] text-black'>Facility</label>
                        <Select
                          className='text-left px-2 py-1 bg-[#f9f9f9] mr-4' sx={{ fontFamily: 'customfont', fontSize: '15px' }}
                          size='small'
                          displayEmpty
                          value={facility_id}
                          onChange={(e) => onFacilityChange(e.target.value)}
                        // label="Facility"
                        >
                          <MenuItem value="" key={0}>
                            <em>N/A</em>
                          </MenuItem>
                          {facilityList?.map((data, index) => {
                            return (
                              <MenuItem value={data.id} key={index + 1}>{data.facilityName}</MenuItem>
                            );
                          })}
                        </Select>
                      </FormControl>
                    </div>
                  </Grid>
                  <Grid sx={{ mt: 0, padding: 0 }} item xs={12} sm={12} md={4} lg={4} xl={4}>
                    <div className="rounded-md -space-y-px">
                      <FormControl fullWidth>
                        {/* <InputLabel id="demo-simple-select-standard-label">Building</InputLabel> */}
                        <label className='text-left font-[customfont] font-medium mb-2 tracking-[1px] text-black'>Building</label>
                        <Select
                          className='text-left px-2 py-1 bg-[#f9f9f9] mr-4' sx={{ fontFamily: 'customfont', fontSize: '15px' }}
                          size='small'
                          displayEmpty
                          value={building_id}
                          onChange={(e) => onBuildingChange(e.target.value)}
                        // label="Building"
                        >
                          <MenuItem value="" key={0} sx={{ fontFamily: 'customfont' }}>
                            <em>N/A</em>
                          </MenuItem>
                          {buildingList?.map((data, index) => {
                            return (
                              <MenuItem value={data.id} key={index + 1} sx={{ fontFamily: 'customfont' }}>{data.buildingName}</MenuItem>
                            );
                          })}
                        </Select>
                      </FormControl>
                    </div>
                  </Grid>
                  <Grid sx={{ mt: 0, padding: 0 }} item xs={12} sm={12} md={4} lg={4} xl={4}>
                    <div className="rounded-md -space-y-px">
                      <FormControl fullWidth>
                        {/* <InputLabel id="demo-simple-select-standard-label">Floor</InputLabel> */}
                        <label className='text-left font-[customfont] font-medium mb-2 tracking-[1px] text-black'>Floor</label>
                        <Select
                          className='text-left px-2 py-1 bg-[#f9f9f9] mr-4' sx={{ fontFamily: 'customfont', fontSize: '15px' }}
                          size='small'
                          displayEmpty
                          value={floor_id}
                          onChange={(e) => onFloorChange(e.target.value)}
                        // label="Floor"
                        >
                          <MenuItem value="" key={0} sx={{ fontFamily: 'customfont' }}>
                            <em>N/A</em>
                          </MenuItem>
                          {floorList?.map((data, index) => {
                            return (
                              <MenuItem value={data.id} key={index + 1} sx={{ fontFamily: 'customfont' }}>{data.floorName}</MenuItem>
                            );
                          })}
                        </Select>
                      </FormControl>
                    </div>
                  </Grid>
                  <Grid sx={{ mt: 0, padding: 0 }} item xs={12} sm={12} md={4} lg={4} xl={4}>
                    <div className="rounded-md -space-y-px">
                      <FormControl fullWidth>
                        {/* <InputLabel id="demo-simple-select-standard-label">Lab</InputLabel> */}
                        <label className='text-left font-[customfont] font-medium mb-2 tracking-[1px] text-black'>Lab</label>
                        <Select
                          className='text-left px-2 py-1 bg-[#f9f9f9] mr-4' sx={{ fontFamily: 'customfont', fontSize: '15px' }}
                          size='small'
                          displayEmpty
                          value={lab_id}
                          onChange={(e) => onLabChange(e.target.value)}
                        // label="Lab"
                        >
                          <MenuItem value="" key={0}>
                            <em>N/A</em>
                          </MenuItem>
                          {labList?.map((data, index) => {
                            return (
                              <MenuItem value={data.id} key={index + 1}>{data.labDepName}</MenuItem>
                            );
                          })}
                        </Select>
                      </FormControl>
                    </div>
                  </Grid>
                </Grid>
              )}
            <div className='flex items-center justify-center min-[320px]:flex-col min-[768px]:flex-row'>
              <div className="rounded-md -space-y-px mb-2 w-full mr-5">
                <TextField
                  fullWidth
                  sx={{ mb: 2, mt: 2, fontFamily: 'customfont' }}
                  label="Employee Id"
                  type="text"
                  value={empId}
                  variant="outlined"
                  // placeholder="Employee Id"
                  className="mb-2 appearance-none rounded-none
                  relative block w-full px-3 py-2 border border-gray-300
                  placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none
                  focus:ring-red-500 focus:border-red-500  sm:text-sm"
                  required
                  onBlur={() => validateForNullValue(empId, 'employeeId')}
                  onChange={(e) => { setEmployeeId(e.target.value); }}
                  autoComplete="off"
                  error={errorObject?.employeeId?.errorStatus}
                  helperText={errorObject?.employeeId?.helperText}
                  InputLabelProps={{
                    shrink: true,
                  }}

                  InputProps={{
                    className: 'bg-[#f9f9f9]',
                    sx: { fontFamily: 'customfont' }
                  }}
                />
              </div>
              {/* <div className="rounded-md -space-y-px"> */}
              <div className="mt-2 w-full mr-4">
                <TextField
                  fullWidth
                  sx={{ mb: 2 }}
                  label="Email Id"
                  type="email"
                  value={email}
                  variant="outlined"
                  // placeholder="Email Id"
                  className="mb-2 appearance-none rounded-none relative
                  block w-full px-3 py-2 border border-gray-300 placeholder-gray-500
                  text-gray-900 rounded-t-md focus:outline-none focus:ring-red-500
                  focus:border-red-500  sm:text-sm"
                  required
                  onBlur={() => { validateForNullValue(email, 'email'); }}
                  onChange={(e) => { setEmailId(e.target.value); }}
                  autoComplete="off"
                  error={errorObject?.emailId?.errorStatus}
                  helperText={errorObject?.emailId?.helperText}
                  InputLabelProps={{
                    shrink: true,
                  }}
                  InputProps={{
                    className: 'bg-[#f9f9f9]',
                    style: { fontFamily: 'customfont' }
                  }}
                />
              </div>
              {/* </div> */}
            </div>
            <div className='flex items-center justify-center min-[320px]:flex-col min-[768px]:flex-row'>
              <div className="rounded-md -space-y-px w-full mr-5">
                <div className="mb-2">
                  <TextField
                    sx={{ mb: 2 }}
                    label="Phone"
                    type="number"
                    value={phoneNo}
                    variant="outlined"
                    // placeholder="Phone number"
                    className="mb-2 appearance-none rounded-none relative block w-full px-3 py-2
                    border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md
                    focus:outline-none focus:ring-red-500 focus:border-red-500  sm:text-sm"
                    required
                    onBlur={() => validateForNullValue(phoneNo, 'phone')}
                    onChange={(e) => { setPhone(e.target.value); }}
                    autoComplete="off"
                    error={errorObject?.phone?.errorStatus}
                    helperText={errorObject?.phone?.helperText}
                    InputLabelProps={{
                      shrink: true,
                      // backgroundCo:'#f9f9f9'

                    }}
                    InputProps={{
                      className: 'bg-[#f9f9f9]',
                      style: { fontFamily: 'customfont' }
                    }}
                  />
                </div>
              </div>
              <div className="rounded-md -space-y-px w-full mr-4">
                <div className="mb-2">
                  <TextField
                    sx={{ mb: 2, fontFamily: 'customfont' }}
                    label="Full Name"
                    type="text"
                    value={empName}
                    variant="outlined"
                    // placeholder="Full Name"
                    className="mb-2 appearance-none rounded-none relative block w-full px-3 py-2
                    border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md
                    focus:outline-none focus:ring-red-500 focus:border-red-500  sm:text-sm"
                    required
                    onBlur={() => validateForNullValue(empName, 'fullName')}
                    onChange={(e) => setFullName(e.target.value)}
                    autoComplete="off"
                    error={errorObject?.fullName?.errorStatus}
                    helperText={errorObject?.fullName?.helperText}
                    InputLabelProps={{
                      shrink: true,
                    }}
                    InputProps={{
                      className: 'bg-[#f9f9f9] ',
                      style: { fontFamily: 'customfont' }
                    }}
                  />
                </div>
              </div>
            </div>
            <div className='flex items-center justify-center min-[320px]:flex-col min-[768px]:flex-row'>
              <div className="rounded-md -space-y-px w-full mr-6">
                <div className="mb-2">
                  <FormControl sx={{ mb: 2 }} fullWidth>
                    <InputLabel id="demo-simple-select-label">Role</InputLabel>
                    {isSuperAdmin
                      ? (
                        <Select
                          className='bg-[#f9f9f9]'
                          sx={{ fontFamily: 'customfont' }}
                          labelId="demo-simple-select-label"
                          id="demo-simple-select"
                          value={empRole}
                          label="Role"
                          onChange={(e) => {
                            setRole(e.target.value);
                          }}
                          disabled
                        >
                          <MenuItem value="superAdmin" sx={{ fontFamily: 'customfont' }}>Super Admin</MenuItem>
                        </Select>
                      )
                      : (
                        <Select
                          className='bg-[#f9f9f9]'
                          sx={{ fontFamily: 'customfont' }}
                          labelId="demo-simple-select-label"
                          id="demo-simple-select"
                          value={empRole}
                          disabled={userRole === 'Manager' && true}
                          label="Role"
                          displayEmpty
                          onChange={(e) => {
                            setRole(e.target.value);
                          }}
                        >
                          <MenuItem value="User" sx={{ fontFamily: 'customfont' }}>User</MenuItem>
                          <MenuItem value="Manager" sx={{ fontFamily: 'customfont' }}>Manager</MenuItem>
                          <MenuItem value="Admin" sx={{ fontFamily: 'customfont' }}>Admin</MenuItem>
                        </Select>
                      )}
                  </FormControl>
                </div>
              </div>

              {isSuperAdmin ? '' :
                <div className="w-full text-right mr-2">
                  <div className="">
                    <FormGroup sx={{ display: 'block' }}>
                      <FormControlLabel
                        control={(
                          <Switch
                            /* eslint-disable-next-line */
                            checked={empNotification}
                            onChange={(e) => {
                              setEmpNotification(e.target.checked);
                            }}
                            color="success"
                          // sx={{color:'black'}}
                          />
                        )}
                        label="Enable Notification"
                      />
                    </FormGroup>
                  </div>
                </div>
              }
            </div>
            <div className=" float-right flex mt-3 min-[320px]:flex-col min-[768px]:flex-row">
              <div className='mt-3 min-[320px]:mb-5 min-[768px]:mb-5  min-[320px]:mr-0 min-[768px]:mr-7' >
                {isAddButton ? ''
                  : (
                    <Button
                     style={{
                      background: 'rgb(19 60 129)',}}
                      sx={{
                        textAlign: 'center',
                        height: '40px',
                        color: 'white',
                        padding: "10px 19px",
                        fontSize: '13px',
                        borderRadius: '10px',
                        fontWeight: '600',
                        fontFamily: 'customfont',
                        letterSpacing: '1px',
                        boxShadow: 'none',
                        marginRight:'20px'
                      }}
                      onClick={() => {
                        setBtnReset(true);
                      }}
                    >
                      Reset Password
                    </Button>
                  )}
              </div>
              <div className='mt-3 min-[320px]:mb-5 min-[768px]:mb-0 min-[320px]:mr-0 min-[768px]:mr-7'>
                <Button
                  style={{
                    background: 'rgb(19 60 129)',}}
                  sx={{
                    height: '40px',
                    color: 'white',
                    padding: "10px 25px",
                    fontSize: '13px',
                    borderRadius: '10px',
                    fontWeight: '600',
                    fontFamily: 'customfont',
                    letterSpacing: '1px',
                    boxShadow: 'none',
                    marginRight:'20px'
                  }}
                  type="submit"
                  disabled={
                    errorObject?.employeeId?.errorStatus
                    || errorObject?.emailId?.errorStatus
                    || errorObject?.phone?.errorStatus
                    || errorObject?.role?.errorStatus
                    || errorObject?.fullName?.errorStatus
                  }
                >
                  {isAddButton ? 'Add' : 'Update'}
                </Button>
              </div>
              <div className='mt-3  min-[320px]:mb-5 min-[768px]:mb-0 min-[320px]:mr-0 min-[768px]:mr-7'>
                <Button
                  style={{
                    background: 'rgb(19 60 129)',}}
                  sx={{
                    height: '40px',
                    color: 'white',
                    padding: "10px 19px",
                    fontSize: '13px',
                    borderRadius: '10px',
                    fontWeight: '600',
                    fontFamily: 'customfont',
                    letterSpacing: '1px',
                    boxShadow: 'none'
                  }}
                  onClick={() => {
                    setErrorObject({});
                    setBranchList([]);
                    setFacilityList([]);
                    setBuildingList([]);
                    setBranchId('');
                    setFacilityId('');
                    setBuildingId('');
                    setFloorId('');
                    setLabId('');
                    loaddata();
                    setOpen(false);
                  }}
                >
                  Cancel
                </Button>
              </div>
            </div>
          </div>
        </form>
      </DialogContent>
      <ConfirmPassword
        open={btnReset}
        passwordSubmit={passwordSubmit}
        setConfirmPassword={setConfirmPassword}
        setBtnReset={setBtnReset}
      />
      <NotificationBar
        handleClose={handleClose}
        notificationContent={openNotification.message}
        openNotification={openNotification.status}
        type={openNotification.type}
      />
      <Backdrop
        sx={{ color: '#fff', zIndex: (theme) => theme.zIndex.drawer + 1 }}
        open={backdrop}
      >
        <CircularProgress color="inherit" />
      </Backdrop>
    </Dialog>
  );
}

export default UserModal;
