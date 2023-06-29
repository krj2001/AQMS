import React, { useEffect, useState } from 'react';
import {
  Button, Dialog, DialogContent, DialogTitle, TextField,
} from '@mui/material';
import InputLabel from '@mui/material/InputLabel';
import MenuItem from '@mui/material/MenuItem';
import FormControl from '@mui/material/FormControl';
import Select from '@mui/material/Select';
import Grid from '@mui/material/Grid';
import MixedChart from '../../../chart/mixedChart/MixedChart';

import { DashboardIndividualSensorDetails } from '../../../../services/LoginPageService';
/* eslint-disable no-unused-vars */
function SensorGraphComponent({
  open, setOpen, sensorTagId, segretionInterval, setSegretionInterval, rangeInterval, setRangeInterval, sensorTag,
}) {
  const [data, setData] = useState([]);
  const [groupingIntervalList, setGroupingIntervalList] = useState([
    { value: '1', text: '1 Minute' },
    { value: '5', text: '5 Minutes' },
  ]);

  useEffect(() => {
    if(sensorTagId){
      DashboardIndividualSensorDetails({ sensorTagId, segretionInterval, rangeInterval }, handleSuccess, handleException);
    }
  }, [sensorTagId, segretionInterval, rangeInterval]);

  const handleSuccess = (dataObject) => {
    setData(dataObject || []);
  };

  const handleException = () => { };

  const updateGroupingInterval = (e) => {
    if (e.target.value === '15') {
      setGroupingIntervalList([
        { value: '1', text: '1 Minute' },
      ]);
    } else if (e.target.value === '60') {
      setGroupingIntervalList([
        { value: '1', text: '1 Minute' },
        { value: '5', text: '5 Minutes' },
      ]);
    } else if (e.target.value === '1440') {
      setGroupingIntervalList([
        { value: '30', text: '30 Minutes' },
        { value: '60', text: '1 Hour' },
      ]);
    } else if (e.target.value === '10080') {
      setGroupingIntervalList([
        { value: '15', text: '15 Minutes' },
      ]);
    } else if (e.target.value === '43200') {
      setGroupingIntervalList([
        { value: '1440', text: '1 Day' },
      ]);
    }
  };

  return (
    <Dialog
      sx={{ '& .MuiDialog-paper': { minWidth: '95%', 
      // minHeight: '95%' 
    } }}
      maxWidth="sm"
      open={open}
    >
      <DialogTitle>
        Trends of Sensor (
        {sensorTag}
        )
      </DialogTitle>
      <DialogContent style={{
        minWidth: '1200px',
        minHeight: '400px',
      }}>
        <Grid container rowSpacing={1} columnSpacing={{ xs: 1, sm: 2, md: 3 }} style={{
          minWidth: 'inherit',
          marginLeft: '0px'
        }}>
          <Grid item xs={6} />
          <Grid item xs={2}>
            <FormControl fullWidth margin="normal" sx={{ marginTop: 1 }}>
              <TextField
                sx={{ marginTop: 0 }}
                margin="dense"
                id="outlined-required"
                label="Sensor Tag"
                defaultValue=""
                fullWidth
                type="text"
                disabled="true"
                value={sensorTag}
                autoComplete="off"
                size="small"
              />
            </FormControl>
          </Grid>
          <Grid item xs={2}>
            <FormControl fullWidth margin="normal" sx={{ marginTop: 1 }}>
              <InputLabel id="demo-simple-select-label">Last </InputLabel>
              <Select
                labelId="demo-simple-select-label"
                id="demo-simple-select"
                value={rangeInterval}
                label="Last"
                size="small"
                onChange={(e) => {
                  setRangeInterval(e.target.value);
                  updateGroupingInterval(e);
                }}
              >
                <MenuItem value="15">15 Min</MenuItem>
                <MenuItem value="60">1 Hr</MenuItem>
                <MenuItem value="1440">24 Hr</MenuItem>
                <MenuItem value="10080">1 week</MenuItem>
                <MenuItem value="43200">1 Month</MenuItem>
              </Select>
            </FormControl>
          </Grid>
          <Grid item xs={2}>
            <FormControl fullWidth margin="normal" sx={{ marginTop: 1 }}>
              <InputLabel id="demo-simple-select-label">Grouping Interval</InputLabel>
              <Select
                sx={{ marginTop: 0 }}
                labelId="demo-simple-select-label"
                id="demo-simple-select"
                value={segretionInterval}
                label="Grouping Interval"
                onChange={(e) => {
                  setSegretionInterval(e.target.value);
                }}
                size="small"
              >
                {
                  /* eslint-disable-next-line */
                  groupingIntervalList.map((groupingIntervalList, key) => <MenuItem key={key} value={groupingIntervalList.value}>{groupingIntervalList.text}</MenuItem>)
                }
              </Select>
            </FormControl>
          </Grid>
        </Grid>
        <Grid container style={{
          minWidth: 'inherit',
          marginLeft: '0px'
        }}>
          <Grid item md={12}>
            <div 
              // className="mt-10" 
              style={{ 
                minWidth: '1200px',
                minHeight: '325px',
                height: '100%', 
                width: '100%' }}>
              <MixedChart data={data} />
            </div>
          </Grid>
        </Grid>
        <Grid item style={{
          minWidth: 'inherit',
          marginLeft: '0px'
        }}>
          <Button
            sx={{ m: 1 }}
            size="large"
            onClick={() => {
              setOpen(false);
            }}
            style={{
              float: 'right'
            }}
          >
            Cancel
          </Button>
        </Grid>
      </DialogContent>
    </Dialog>
  );
}

export default SensorGraphComponent;
