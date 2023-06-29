import {
  Grid, TextField,
} from '@mui/material';
import React from 'react';
import { useUserAccess } from '../../../../../context/UserAccessProvider';
import ApplicationStore from '../../../../../utils/localStorageUtil';
import { AnalogSensorValidate } from '../../../../../validation/formValidation';

function SensorAlertRange({
  errorObject, setErrorObject,
  criticalMinValue, setCriticalMinValue,
  criticalMaxValue, setCriticalMaxValue,
  criticalRefMinValue, criticalRefMaxValue,
  warningMinValue, setWarningMinValue,
  warningMaxValue, setWarningMaxValue,
  warningRefMinValue, warningRefMaxValue, 
  outofrangeMinValue, setOutofrangeMinValue,
  outofrangeMaxValue, setOutofrangeMaxValue,
  outofrangeRefMinValue, outofrangeRefMaxValue
}) {
  const moduleAccess = useUserAccess()('devicelocation');
  const { userDetails } = ApplicationStore().getStorage('userDetails');
  const validateForNullValue = (value, type) => {
    AnalogSensorValidate(value, type, setErrorObject);
  };

  const validateAlertrange = (currentValue, minimumValue, maximumvalue, setCurrentValue) =>{
    if(parseInt(currentValue)<parseInt(minimumValue)){
      userDetails.userRole !== 'systemSpecialist' && setCurrentValue(minimumValue);
    }
    if(parseInt(currentValue)>parseInt(maximumvalue)){
      userDetails.userRole !== 'systemSpecialist' && setCurrentValue(maximumvalue);
    }
  }

  return (
    <Grid sx={{ px: 0, p: 0 }}>
      <Grid container spacing={2} sx={{ mt: 1 }} />
      <Grid container spacing={2} sx={{ mt: 1 }}>
        <Grid
          sx={{ mt: 0, padding: 0 }}
          item
          xs={12}
          sm={12}
          md={12}
          lg={12}
          xl={12}
        >
          <div className="float-left ml-2">
            Critical Alert :
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
              value={criticalMinValue}
              disabled={moduleAccess.edit === false && true}
              type="number"
              inputProps={{
                min: userDetails.userRole !== 'systemSpecialist' && criticalRefMinValue,
                max: userDetails.userRole !== 'systemSpecialist' && criticalRefMaxValue,
              }}
              onBlur={() => {
                validateForNullValue(criticalMinValue, 'criticalMinValue');
                validateAlertrange(criticalMinValue, criticalRefMinValue, criticalRefMaxValue, setCriticalMinValue);
              }}
              onChange={(e) => {
                setCriticalMinValue(e.target.value);
              }}
              margin="normal"
              required
              id="outlined-required"
              label="Min Value"
              fullWidth
              error={errorObject?.criticalMinValue?.errorStatus}
              helperText={errorObject?.criticalMinValue?.helperText}
              autoComplete="off"
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
              value={criticalMaxValue}
              type="number"
              inputProps={{
                min: userDetails.userRole !== 'systemSpecialist' && criticalRefMinValue,
                max: userDetails.userRole !== 'systemSpecialist' && criticalRefMaxValue,
              }}
              disabled={moduleAccess.edit === false && true}
              onBlur={() => {
                validateForNullValue(criticalMaxValue, 'criticalMaxValue');
                validateAlertrange(criticalMaxValue, criticalRefMinValue, criticalRefMaxValue, setCriticalMaxValue);
              }}
              onChange={(e) => {
                setCriticalMaxValue(e.target.value);
              }}
              margin="normal"
              required
              id="outlined-required"
              label="Max Value"
              fullWidth
              error={errorObject?.criticalMaxValue?.errorStatus}
              helperText={errorObject?.criticalMaxValue?.helperText}
              autoComplete="off"
            />
          </div>
        </Grid>
      </Grid>
      <Grid container spacing={2} sx={{ mt: 1 }}>
        <Grid
          sx={{ mt: 0, padding: 0 }}
          item
          xs={12}
          sm={12}
          md={12}
          lg={12}
          xl={12}
        >
          <div className="float-left ml-2">
            Warning Alert :
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
              value={warningMinValue}
              type="number"
              inputProps={{
                min: userDetails.userRole !== 'systemSpecialist' && warningRefMinValue,
                max: userDetails.userRole !== 'systemSpecialist' && warningRefMaxValue,
              }}
              disabled={moduleAccess.edit === false && true}
              onBlur={() => {
                validateForNullValue(warningMinValue, 'warningMinValue');
                validateAlertrange(warningMinValue, warningRefMinValue, warningRefMaxValue, setWarningMinValue);
              }}
              onChange={(e) => {
                setWarningMinValue(e.target.value);
              }}
              margin="normal"
              required
              id="outlined-required"
              label="Min Value"
              fullWidth
              error={errorObject?.warningMinValue?.errorStatus}
              helperText={errorObject?.warningMinValue?.helperText}
              autoComplete="off"
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
              value={warningMaxValue}
              type="number"
              inputProps={{
                min: userDetails.userRole !== 'systemSpecialist' && warningRefMinValue,
                max: userDetails.userRole !== 'systemSpecialist' && warningRefMaxValue,
              }}
              disabled={moduleAccess.edit === false && true}
              onBlur={() => {
                validateForNullValue(warningMaxValue, 'warningMaxValue');
                validateAlertrange(warningMaxValue, warningRefMinValue, warningRefMaxValue, setWarningMaxValue);
              }}
              onChange={(e) => {
                setWarningMaxValue(e.target.value);
              }}
              margin="normal"
              required
              id="outlined-required"
              label="Max Value"
              fullWidth
              error={errorObject?.warningMaxValue?.errorStatus}
              helperText={errorObject?.warningMaxValue?.helperText}
              autoComplete="off"
            />
          </div>
        </Grid>
      </Grid>
      <Grid container spacing={2} sx={{ mt: 1 }}>
        <Grid
          sx={{ mt: 0, padding: 0 }}
          item
          xs={12}
          sm={12}
          md={12}
          lg={12}
          xl={12}
        >
          <div className="float-left ml-2">
            Out-of-Range Alert :
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
              value={outofrangeMinValue}
              type="number"
              inputProps={{
                min: userDetails.userRole !== 'systemSpecialist' && outofrangeRefMinValue,
                max: userDetails.userRole !== 'systemSpecialist' && outofrangeRefMaxValue,
              }}
              disabled={moduleAccess.edit === false && true}
              onBlur={() => {
                validateForNullValue(outofrangeMinValue, 'outofrangeMinValue');
                validateAlertrange(outofrangeMinValue, outofrangeRefMinValue, outofrangeRefMaxValue, setOutofrangeMinValue);
              }}
              onChange={(e) => {
                setOutofrangeMinValue(e.target.value);
              }}
              margin="normal"
              required
              id="outlined-required"
              label="Min Value"
              fullWidth
              error={errorObject?.outofrangeMinValue?.errorStatus}
              helperText={errorObject?.outofrangeMinValue?.helperText}
              autoComplete="off"
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
              value={outofrangeMaxValue}
              type="number"
              inputProps={{
                min: userDetails.userRole !== 'systemSpecialist' && outofrangeRefMinValue,
                max: userDetails.userRole !== 'systemSpecialist' && outofrangeRefMaxValue,
              }}
              disabled={moduleAccess.edit === false && true}
              onBlur={() => {
                validateForNullValue(outofrangeMaxValue, 'outofrangeMaxValue');
                validateAlertrange(outofrangeMaxValue, outofrangeRefMinValue, outofrangeRefMaxValue, setOutofrangeMaxValue);
              }}
              onChange={(e) => {
                setOutofrangeMaxValue(e.target.value);
              }}
              margin="normal"
              required
              id="outlined-required"
              label="Max Value"
              fullWidth
              error={errorObject?.outofrangeMaxValue?.errorStatus}
              helperText={errorObject?.outofrangeMaxValue?.helperText}
              autoComplete="off"
            />
          </div>
        </Grid>
      </Grid>
    </Grid>
  );
}

export default SensorAlertRange;
