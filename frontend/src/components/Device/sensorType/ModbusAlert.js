import {
  DialogContent, FormControl, Grid, InputLabel, MenuItem, Select, TextField,
} from '@mui/material';
import React from 'react';
import { useUserAccess } from '../../../context/UserAccessProvider';
import ApplicationStore from '../../../utils/localStorageUtil';

function ModbusAlert({
  pollingIntervalType, setPollingIntervalType,
  criticalMinValue, setCriticalMinValue,
  criticalMaxValue, setCriticalMaxValue,
  criticalAlertType, setCriticalAlertType,
  criticalLowAlert, setCriticalLowAlert,
  criticalHighAlert, setCriticalHighAlert,
  criticalRefMinValue, criticalRefMaxValue,

  warningMinValue, setWarningMinValue,
  warningMaxValue, setWarningMaxValue,
  warningAlertType, setWarningAlertType,
  warningLowAlert, setWarningLowAlert,
  warningHighAlert, setWarningHighAlert,
  warningRefMinValue, warningRefMaxValue,

  outofrangeMinValue, setOutofrangeMinValue,
  outofrangeMaxValue, setOutofrangeMaxValue,
  outofrangeAlertType, setOutofrangeAlertType,
  outofrangeLowAlert, setOutofrangeLowAlert,
  outofrangeHighAlert, setOutofrangeHighAlert,
  outofrangeRefMinValue, outofrangeRefMaxValue,
}) {
  const { userDetails } = ApplicationStore().getStorage('userDetails');
  const moduleAccess = useUserAccess()('devicelocation');

  const validateAlertrange = (currentValue, minimumValue, maximumvalue, setCurrentValue) =>{
    if(parseInt(currentValue)<parseInt(minimumValue)){
      userDetails.userRole !== 'systemSpecialist' && setCurrentValue(minimumValue);
    }
    if(parseInt(currentValue)>parseInt(maximumvalue)){
      userDetails.userRole !== 'systemSpecialist' && setCurrentValue(maximumvalue);
    }
  }

  return (
    <DialogContent sx={{ px: 0, p: 0 }}>
      <Grid container spacing={2} sx={{ mt: 1 }}>
        <Grid
          sx={{ mt: 0, padding: 0 }}
          item
          xs={12}
          sm={6}
          md={6}
          lg={6}
          xl={6}
        >
          <FormControl fullWidth margin="normal" sx={{ marginTop: 0 }} disabled={moduleAccess.edit === false && true}>
            <InputLabel id="demo-simple-select-label">
              Polling Interval Type *
            </InputLabel>
            <Select
              sx={{ minWidth: 250 }}
              labelId="demo-simple-select-label"
              value={pollingIntervalType}
              label="Polling Interval type *"
              onChange={(e) => {
                setPollingIntervalType(e.target.value);
              }}
              // error={errorObject?.deviceName?.errorStatus}
              // helperText={errorObject?.deviceName?.helperText}
            >
              <MenuItem value="Priority">Priority</MenuItem>
              <MenuItem value="NonPriority">Non Priority</MenuItem>
            </Select>
          </FormControl>
        </Grid>
      </Grid>
      <Grid container spacing={2} sx={{ mt: 0 }}>
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
          sm={12}
          md={2}
          lg={2}
          xl={2}
        >
          <FormControl fullWidth margin="normal" sx={{ marginTop: 0 }} disabled={moduleAccess.edit === false && true}>
            <InputLabel id="demo-simple-select-label">
              Sensor alert
            </InputLabel>
            <Select
              // sx={{ minWidth: 250 }}
              labelId="demo-simple-select-label"
              value={criticalAlertType}
              label="Sensor alert"
              onChange={(e) => {
                setCriticalAlertType(e.target.value);
                setCriticalMinValue(criticalMinValue);
                setCriticalMaxValue(criticalMaxValue);
              }}
              // error={errorObject?.deviceName?.errorStatus}
              // helperText={errorObject?.deviceName?.helperText}
            >
              <MenuItem value="High">High</MenuItem>
              <MenuItem value="Low">Low</MenuItem>
              <MenuItem value="Both">Both</MenuItem>
            </Select>
          </FormControl>
        </Grid>
        <Grid
          sx={{ mt: 0, padding: 0, display: 'flex' }}
          item
          xs={12}
          sm={12}
          md={10}
          lg={10}
          xl={10}
          // style={{float:'right'}}
        >
          <Grid
            container
            spacing={1}
          >

            {criticalAlertType === 'Low' || criticalAlertType === 'Both'
              ? (
                <Grid
                  sx={{ mt: 0, padding: 0 }}
                  item
                  xs={12}
                  sm={12}
                  md={6}
                  lg={6}
                  xl={6}
                >
                  <div className="rounded-md -space-y-px">
                    <TextField
                      sx={{ marginTop: 0 }}
                      value={criticalLowAlert}
                      disabled={moduleAccess.edit === false && true}
                      // onBlur={() => validateForNullValue(alertTag, "alertTag")}
                      onChange={(e) => {
                        setCriticalLowAlert(e.target.value);
                      }}
                      margin="normal"
                      required
                      id="outlined-required"
                      label="Low alert message"
                      fullWidth
                      // error={errorObject?.deviceName?.errorStatus}
                      // helperText={errorObject?.deviceName?.helperText}
                      autoComplete="off"
                    />
                  </div>
                </Grid>
              )
              : ''}
            {criticalAlertType === 'Both' || criticalAlertType === 'High'
              ? (
                <Grid
                  sx={{ mt: 0, padding: 0 }}
                  item
                  xs={12}
                  sm={12}
                  md={6}
                  lg={6}
                  xl={6}
                >
                  <div className="rounded-md -space-y-px">
                    <TextField
                      sx={{ marginTop: 0 }}
                      value={criticalHighAlert}
                      disabled={moduleAccess.edit === false && true}
                      // onBlur={() => validateForNullValue(alertTag, "alertTag")}
                      onChange={(e) => {
                        setCriticalHighAlert(e.target.value);
                      }}
                      margin="normal"
                      required
                      id="outlined-required"
                      label="High alert message"
                      fullWidth
                      // error={errorObject?.deviceName?.errorStatus}
                      // helperText={errorObject?.deviceName?.helperText}
                      autoComplete="off"
                    />
                  </div>
                </Grid>
              )
              : ''}
          </Grid>
        </Grid>
        <Grid
          sx={{ mt: 0, padding: 0 }}
          item
          xs={6}
          sm={6}
          md={6}
          lg={6}
          xl={6}
        >
          <div className="rounded-md -space-y-px">
            <TextField
              sx={{ marginTop: 0 }}
              value={criticalMinValue}
              disabled={criticalAlertType === 'High' || criticalAlertType === 'High' || (moduleAccess.edit === false && true)}
              type='number'
              inputProps={{
                min: userDetails.userRole !== 'systemSpecialist' && criticalRefMinValue,
                max: userDetails.userRole !== 'systemSpecialist' && criticalRefMaxValue,
                step: 0.01
              }}
              onBlur={() => {
                // validateForNullValue(alertTag, "alertTag");
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
              // error={errorObject?.deviceName?.errorStatus}
              // helperText={errorObject?.deviceName?.helperText}
              autoComplete="off"
            />
          </div>
        </Grid>
        <Grid
          sx={{ mt: 0, padding: 0 }}
          item
          xs={6}
          sm={6}
          md={6}
          lg={6}
          xl={6}
        >
          <div className="rounded-md -space-y-px">
            <TextField
              sx={{ marginTop: 0 }}
              value={criticalMaxValue}
              disabled={criticalAlertType === 'Low' || criticalAlertType === '' || (moduleAccess.edit === false && true)}
              type='number'
              inputProps={{
                min: userDetails.userRole !== 'systemSpecialist' && criticalRefMinValue,
                max: userDetails.userRole !== 'systemSpecialist' && criticalRefMaxValue,
                step: 0.01
              }}
              onBlur={() => {
                // validateForNullValue(alertTag, "alertTag");
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
              // error={errorObject?.deviceName?.errorStatus}
              // helperText={errorObject?.deviceName?.helperText}
              autoComplete="off"
            />
          </div>
        </Grid>
      </Grid>
      <Grid container spacing={2} sx={{ mt: 0 }}>
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
          sm={12}
          md={2}
          lg={2}
          xl={2}
        >
          <FormControl fullWidth margin="normal" sx={{ marginTop: 0 }} disabled={moduleAccess.edit === false && true}>
            <InputLabel id="demo-simple-select-label">
              Sensor alert
            </InputLabel>
            <Select
              // sx={{ minWidth: 250 }}
              labelId="demo-simple-select-label"
              value={warningAlertType}
              label="Sensor alert"
              onChange={(e) => {
                setWarningAlertType(e.target.value);
                setWarningMinValue(warningMinValue);
                setWarningMaxValue(warningMaxValue);
              }}
              // error={errorObject?.deviceName?.errorStatus}
              // helperText={errorObject?.deviceName?.helperText}
            >
              <MenuItem value="High">High</MenuItem>
              <MenuItem value="Low">Low</MenuItem>
              <MenuItem value="Both">Both</MenuItem>
            </Select>
          </FormControl>
        </Grid>
        <Grid
          sx={{ mt: 0, padding: 0, display: 'flex' }}
          item
          xs={12}
          sm={12}
          md={10}
          lg={10}
          xl={10}
          style={{
            // float:'right'
          }}
        >
          <Grid
            container
            spacing={2}
          >

            {warningAlertType === 'Low' || warningAlertType === 'Both'
              ? (
                <Grid
                  sx={{ mt: 0, padding: 0 }}
                  item
                  xs={12}
                  sm={12}
                  md={6}
                  lg={6}
                  xl={6}
                >
                  <div className="rounded-md -space-y-px">
                    <TextField
                      sx={{ marginTop: 0 }}
                      value={warningLowAlert}
                      disabled={moduleAccess.edit === false && true}
                      // onBlur={() => validateForNullValue(alertTag, "alertTag")}
                      onChange={(e) => {
                        setWarningLowAlert(e.target.value);
                      }}
                      margin="normal"
                      required
                      id="outlined-required"
                      label="Low alert message"
                      fullWidth
                      // error={errorObject?.deviceName?.errorStatus}
                      // helperText={errorObject?.deviceName?.helperText}
                      autoComplete="off"
                    />
                  </div>
                </Grid>
              )
              : ''}
            {warningAlertType === 'High' || warningAlertType === 'Both'
              ? (
                <Grid
                  sx={{ mt: 0, padding: 0 }}
                  item
                  xs={12}
                  sm={12}
                  md={6}
                  lg={6}
                  xl={6}
                >
                  <div className="rounded-md -space-y-px">
                    <TextField
                      sx={{ marginTop: 0 }}
                      value={warningHighAlert}
                      disabled={moduleAccess.edit === false && true}
                      // onBlur={() => validateForNullValue(alertTag, "alertTag")}
                      onChange={(e) => {
                        setWarningHighAlert(e.target.value);
                      }}
                      margin="normal"
                      required
                      id="outlined-required"
                      label="High alert message"
                      fullWidth
                      // error={errorObject?.deviceName?.errorStatus}
                      // helperText={errorObject?.deviceName?.helperText}
                      autoComplete="off"
                    />
                  </div>
                </Grid>
              )
              : ''}
          </Grid>
        </Grid>
        <Grid
          sx={{ mt: 0, padding: 0 }}
          item
          xs={6}
          sm={6}
          md={6}
          lg={6}
          xl={6}
        >
          <div className="rounded-md -space-y-px">
            <TextField
              sx={{ marginTop: 0 }}
              value={warningMinValue}
              disabled={warningAlertType === 'High' || warningAlertType === '' || (moduleAccess.edit === false && true)}
              type='number'
              onBlur={() => {
                // validateForNullValue(alertTag, "alertTag");
                validateAlertrange(warningMinValue, warningRefMinValue, warningRefMaxValue, setWarningMinValue);
              }}
              inputProps={{
                min: userDetails.userRole !== 'systemSpecialist' && warningRefMinValue,
                max: userDetails.userRole !== 'systemSpecialist' && warningRefMaxValue,
                step: 0.01
              }}
              onChange={(e) => {
                setWarningMinValue(e.target.value);
              }}
              margin="normal"
              required
              id="outlined-required"
              label="Min Value"
              fullWidth
              // error={errorObject?.deviceName?.errorStatus}
              // helperText={errorObject?.deviceName?.helperText}
              autoComplete="off"
            />
          </div>
        </Grid>
        <Grid
          sx={{ mt: 0, padding: 0 }}
          item
          xs={6}
          sm={6}
          md={6}
          lg={6}
          xl={6}
        >
          <div className="rounded-md -space-y-px">
            <TextField
              sx={{ marginTop: 0 }}
              value={warningMaxValue}
              disabled={warningAlertType === 'Low' || warningAlertType === '' || (moduleAccess.edit === false && true)}
              type='number'
              onBlur={() => {
                // validateForNullValue(alertTag, "alertTag");
                validateAlertrange(warningMaxValue, warningRefMinValue, warningRefMaxValue, setWarningMaxValue);
              }}
              inputProps={{
                min: userDetails.userRole !== 'systemSpecialist' && warningRefMinValue,
                max: userDetails.userRole !== 'systemSpecialist' && warningRefMaxValue,
                step: 0.01
              }}
              onChange={(e) => {
                setWarningMaxValue(e.target.value);
              }}
              margin="normal"
              required
              id="outlined-required"
              label="Max Value"
              fullWidth
              // error={errorObject?.deviceName?.errorStatus}
              // helperText={errorObject?.deviceName?.helperText}
              autoComplete="off"
            />
          </div>
        </Grid>
      </Grid>
      <Grid container spacing={2} sx={{ mt: 0 }}>
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
          sm={12}
          md={2}
          lg={2}
          xl={2}
        >
          <FormControl fullWidth margin="normal" sx={{ marginTop: 0 }} disabled={moduleAccess.edit === false && true}>
            <InputLabel id="demo-simple-select-label">
              Sensor alert
            </InputLabel>
            <Select
              // sx={{ minWidth: 250 }}
              labelId="demo-simple-select-label"
              value={outofrangeAlertType}
              label="Sensor alert"
              onChange={(e) => {
                setOutofrangeAlertType(e.target.value);
                setOutofrangeMinValue(outofrangeMinValue);
                setOutofrangeMaxValue(outofrangeMaxValue);
              }}
              // error={errorObject?.deviceName?.errorStatus}
              // helperText={errorObject?.deviceName?.helperText}
            >
              <MenuItem value="High">High</MenuItem>
              <MenuItem value="Low">Low</MenuItem>
              <MenuItem value="Both">Both</MenuItem>
            </Select>
          </FormControl>
        </Grid>
        <Grid
          sx={{ mt: 0, padding: 0, display: 'flex' }}
          item
          xs={12}
          sm={12}
          md={10}
          lg={10}
          xl={10}
          style={{
            // float:'right'
          }}
        >
          <Grid
            container
            spacing={1}
          >

            {outofrangeAlertType === 'Low' || outofrangeAlertType === 'Both'
              ? (
                <Grid
                  sx={{ mt: 0, padding: 0 }}
                  item
                  xs={12}
                  sm={12}
                  md={6}
                  lg={6}
                  xl={6}
                >
                  <div className="rounded-md -space-y-px">
                    <TextField
                      sx={{ marginTop: 0 }}
                      value={outofrangeLowAlert}
                      disabled={moduleAccess.edit === false && true}
                      // onBlur={() => validateForNullValue(alertTag, "alertTag")}
                      onChange={(e) => {
                        setOutofrangeLowAlert(e.target.value);
                      }}
                      margin="normal"
                      required
                      id="outlined-required"
                      label="Low alert message"
                      fullWidth
                      // error={errorObject?.deviceName?.errorStatus}
                      // helperText={errorObject?.deviceName?.helperText}
                      autoComplete="off"
                    />
                  </div>
                </Grid>
              )
              : ''}
            {outofrangeAlertType === 'High' || outofrangeAlertType === 'Both'
              ? (
                <Grid
                  sx={{ mt: 0, padding: 0 }}
                  item
                  xs={12}
                  sm={12}
                  md={6}
                  lg={6}
                  xl={6}
                >
                  <div className="rounded-md -space-y-px">
                    <TextField
                      sx={{ marginTop: 0 }}
                      value={outofrangeHighAlert}
                      disabled={moduleAccess.edit === false && true}
                      // onBlur={() => validateForNullValue(alertTag, "alertTag")}
                      onChange={(e) => {
                        setOutofrangeHighAlert(e.target.value);
                      }}
                      margin="normal"
                      required
                      id="outlined-required"
                      label="High alert message"
                      fullWidth
                      // error={errorObject?.deviceName?.errorStatus}
                      // helperText={errorObject?.deviceName?.helperText}
                      autoComplete="off"
                    />
                  </div>
                </Grid>
              )
              : ''}
          </Grid>
        </Grid>
        <Grid
          sx={{ mt: 0, padding: 0 }}
          item
          xs={6}
          sm={6}
          md={6}
          lg={6}
          xl={6}
        >
          <div className="rounded-md -space-y-px">
            <TextField
              sx={{ marginTop: 0 }}
              value={outofrangeMinValue}
              disabled={outofrangeAlertType === 'High' || outofrangeAlertType === '' || (moduleAccess.edit === false && true)}
              type='number'
              onBlur={() => {
                // validateForNullValue(alertTag, "alertTag");
                validateAlertrange(outofrangeMinValue, outofrangeRefMinValue, outofrangeRefMaxValue, setOutofrangeMinValue);
              }}
              inputProps={{
                min: userDetails.userRole !== 'systemSpecialist' && outofrangeRefMinValue,
                max: userDetails.userRole !== 'systemSpecialist' && outofrangeRefMaxValue,
                step: 0.01
              }}
              onChange={(e) => {
                setOutofrangeMinValue(e.target.value);
              }}
              margin="normal"
              required
              id="outlined-required"
              label="Min Value"
              fullWidth
              // error={errorObject?.deviceName?.errorStatus}
              // helperText={errorObject?.deviceName?.helperText}
              autoComplete="off"
            />
          </div>
        </Grid>
        <Grid
          sx={{ mt: 0, padding: 0 }}
          item
          xs={6}
          sm={6}
          md={6}
          lg={6}
          xl={6}
        >
          <div className="rounded-md -space-y-px">
            <TextField
              sx={{ marginTop: 0 }}
              value={outofrangeMaxValue}
              disabled={outofrangeAlertType === 'Low' || outofrangeAlertType === '' || (moduleAccess.edit === false && true)}
              type='number'
              onBlur={() => {
                // validateForNullValue(alertTag, "alertTag");
                validateAlertrange(outofrangeMaxValue, outofrangeRefMinValue, outofrangeRefMaxValue, setOutofrangeMaxValue);
              }}
              inputProps={{
                min: userDetails.userRole !== 'systemSpecialist' && outofrangeRefMinValue,
                max: userDetails.userRole !== 'systemSpecialist' && outofrangeRefMaxValue,
                step: 0.01
              }}
              onChange={(e) => {
                setOutofrangeMaxValue(e.target.value);
              }}
              margin="normal"
              required
              id="outlined-required"
              label="Max Value"
              fullWidth
              // error={errorObject?.deviceName?.errorStatus}
              // helperText={errorObject?.deviceName?.helperText}
              autoComplete="off"
            />
          </div>
        </Grid>
      </Grid>
    </DialogContent>
  );
}

export default ModbusAlert;
