import {
  Button, Dialog, DialogContent, DialogTitle, TextField,
} from '@mui/material';
import React, { useEffect, useState } from 'react';
import DialogActions from '@mui/material/DialogActions';
import { AddCategoryValidate } from '../../../../../validation/formValidation';
import {
  SensorCategoryAddService, SensorCategoryEditService,
} from '../../../../../services/LoginPageService';
import NotificationBar from '../../../../notification/ServiceNotificationBar';
import MeasureUnit from './MeasureUnit';

function SensorCategoryModal({
  open, setOpen, isAddButton, categoryData, setRefreshData, handleClose, openNotification, setNotification
}) {
  const [id, setId] = useState('');
  const [sensorName, setCategoryName] = useState('');
  const [sensorDescriptions, setCategoryDescription] = useState('');
  const [errorObject, setErrorObject] = useState({});
  const [unitId, setUnitId] = useState('');
  const [unitLabel, setUnitLabel] = useState('');
  const [unitMeasure, setUnitMeasure] = useState('');
  const [measureUnits, setMeasureUnits] = useState([]);
  const [isAddUnit, setIsAddUnit] = useState(true);

  // const [openNotification, setNotification] = useState({
  //   status: false,
  //   type: 'error',
  //   message: '',
  // });
  useEffect(() => {
    setOpen(open);
    loadData();
  }, [categoryData]);

  const loadData = () => {
    setId(categoryData?.id || '');
    setCategoryName(categoryData?.sensorName || '');
    setCategoryDescription(categoryData?.sensorDescriptions || '');
    if(categoryData?.measureUnitList){
      setMeasureUnits(JSON.parse(categoryData?.measureUnitList?.replace(/\\/g, '').replace(/(^"|"$)/g, '')) || []);
    } else {
      setMeasureUnits([]);
    }
  };

  const clearForm = () => {
    setId('');
    setCategoryName('');
    setCategoryDescription('');
    setMeasureUnits([]);
  }

  const validateForNullValue = (value, type) => {
    AddCategoryValidate(value, type, setErrorObject);
  };

  const handleAddSuccess = (dataObject) => {
    handleSuccess(dataObject);
    clearForm();
  }

  const handleUpdateSuccess = (dataObject) => {
    handleSuccess(dataObject);
    setTimeout(() => {
      setOpen(false);
    }, 3000);
  }

  const handleSuccess = (dataObject) => {
    setNotification({
      status: true,
      type: 'success',
      message: dataObject.message,
    });
    setRefreshData((oldvalue) => !oldvalue);
    setMeasureUnits([]);
  };

  /* eslint-disable-next-line */
  const handleException = (errorObject, errorMessage) => {
    setNotification({
      status: true,
      type: 'error',
      message: errorMessage,
    });
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (isAddButton) {
      /* eslint-disable-next-line */
      SensorCategoryAddService({ sensorName, sensorDescriptions, measureUnitList: JSON.stringify(measureUnits) }, handleAddSuccess, handleException);
    } else {
      SensorCategoryEditService({ id, sensorName, sensorDescriptions, measureUnitList: JSON.stringify(measureUnits) }, handleUpdateSuccess, handleException);
    }
  };

  // const handleClose = () => {
  //   setNotification({
  //     status: false,
  //     type: '',
  //     message: '',
  //   });
  // };

  const addMeasureUnits = () => {
    if (unitLabel === '') {
      setErrorObject((oldData) => {
        const status = {
          errorStatus: true,
          helperText: 'Enter valid unit Label',
        };
        return {
          ...oldData,
          unitLabel: status,
        };
      });
    } else if (measureUnits === '') {
      setErrorObject((oldData) => {
        const status = {
          errorStatus: true,
          helperText: 'Enter valid unit Measure',
        };
        return {
          ...oldData,
          unitMeasure: status,
        };
      });
    } else if (unitId === '') {
      const newMeasureUnits = [...measureUnits, { unitLabel, unitMeasure }];
      setMeasureUnits(newMeasureUnits);
      setUnitMeasure('');
      setUnitLabel('');
    } else {
      const newMeasureUnits = [...measureUnits];
      newMeasureUnits[unitId].unitLabel = unitLabel;
      newMeasureUnits[unitId].unitMeasure = unitMeasure;
      setMeasureUnits(newMeasureUnits);
      setUnitMeasure('');
      setUnitLabel('');
      setIsAddUnit(true);
      setUnitId('');
    }
  };

  const updateMeasureUnits = (index) => {
    setUnitId(index);
    setUnitLabel(measureUnits[index].unitLabel);
    setUnitMeasure(measureUnits[index].unitMeasure);
    setIsAddUnit(false);
  };

  const removeMeasureUnits = (index) => {
    const newMeasureUnits = [...measureUnits];
    newMeasureUnits.splice(index, 1);
    setMeasureUnits(newMeasureUnits);
  };

  const clearMeasureUnits = () => {
    setUnitMeasure('');
    setUnitLabel('');
    setErrorObject('');
  };

  return (
    <Dialog
      fullWidth
      maxWidth="md"
      sx={{ '& .MuiDialog-paper': { width: '80%', maxHeight: '100%' } }}
      open={open}
    >
      <form onSubmit={handleSubmit}>
        <DialogTitle
          sx={{ textAlign: 'center', fontFamily: 'customfont', fontWeight: '600', marginTop: '8px', marginBottom: '15px', letterSpacing: '1px' }}>
          {isAddButton ? 'Add Category' : 'Edit Category'}
        </DialogTitle>
        <DialogContent
          sx={{ padding: '0px 24px' }}
          className='flex w-full gap-7 mt-5 min-[320px]:flex-col min-[768px]:flex-row'>
          <div className='w-full'>
            <TextField
              margin="dense"
              id="outlined-required"
              label="Category Name"
              defaultValue=""
              fullWidth
              value={sensorName}
              required
              onBlur={() => validateForNullValue(sensorName, 'categoryName')}
              onChange={(e) => { setCategoryName(e.target.value); }}
              autoComplete="off"
              error={errorObject?.categoryName?.errorStatus}
              helperText={errorObject?.categoryName?.helperText}
              InputLabelProps={{
                shrink: true,
              }}
            />
          </div>
          <div className='w-full'>
            <TextField
              id="dense"
              label="Category Descriptions"
              multiline
              margin="dense"
              maxRows={4}
              fullWidth
              value={sensorDescriptions}
              required
              onBlur={() => validateForNullValue(sensorDescriptions, 'categoryDescription')}
              onChange={(e) => { setCategoryDescription(e.target.value); }}
              autoComplete="off"
              error={errorObject?.categoryDescription?.errorStatus}
              helperText={errorObject?.categoryDescription?.helperText}
              InputLabelProps={{
                shrink: true,
              }}
            />
          </div>
        </DialogContent>
        <DialogContent style={{ overflow: 'hidden' }} >
          <div className="flex items-center justify-between items-center gap-7 w-full min-[320px]:flex-col min-[768px]:flex-row">
            <div className='w-full'>
              <TextField
                sx={{ marginBottom: '13px' }}
                id="dense"
                label="Units label"
                multiline
                margin="dense"
                maxRows={4}
                fullWidth
                value={unitLabel}
                /* eslint-disable-next-line */
                onBlur={() => validateForNullValue(unitLabel, 'unitLabel')}
                onChange={(e) => { setUnitLabel(e.target.value); }}
                autoComplete="off"
                error={errorObject?.unitLabel?.errorStatus}
                helperText={errorObject?.unitLabel?.helperText}
                InputLabelProps={{
                  shrink: true,
                }}
              />
              <TextField
                id="dense"
                multiline
                label="Units Measure"
                margin="dense"
                maxRows={4}
                fullWidth
                value={unitMeasure}
                onBlur={() => validateForNullValue(unitMeasure, 'unitMeasure')}
                onChange={(e) => { setUnitMeasure(e.target.value); }}
                autoComplete="off"
                error={errorObject?.unitMeasure?.errorStatus}
                helperText={errorObject?.unitMeasure?.helperText}
                InputLabelProps={{
                  shrink: true,
                }}
              />
            </div>
            <div className='flex flex-col w-full '>
              <Button
                style={{
                  background: 'rgb(19 60 129)',}}
                sx={{
                  width: '120px',
                  color: 'white',
                  padding: "8px 19px",
                  marginRight: 'auto',
                  marginLeft: 'auto',
                  marginBottom: '15px',
                  fontSize: '13px',
                  borderRadius: '10px',
                  fontWeight: '600',
                  fontFamily: 'customfont',
                  letterSpacing: '1px',
                }}
                size="large"
                autoFocus
                disabled={errorObject?.unitLabel?.errorStatus || errorObject?.unitMeasure?.errorStatus}
                onClick={addMeasureUnits}
              >
                {isAddUnit ? 'Add Unit' : 'Update Unit'}
              </Button>
              <Button
                style={{
                  background: 'rgb(19 60 129)',}}
                sx={{
                  width: '120px',
                  color: 'white',
                  padding: "8px 19px",
                  marginRight: 'auto',
                  marginLeft: 'auto',
                  marginBottom: '1px',
                  fontSize: '13px',
                  borderRadius: '10px',
                  fontWeight: '600',
                  fontFamily: 'customfont',
                  letterSpacing: '1px',
                }}
                size="large"
                autoFocus
                onClick={clearMeasureUnits}
              >
                Cancel
              </Button>
            </div>
          </div>
          <div className="w-full mt-5 ">
            <div className="todo-container mr-auto ml-auto w-full">
              <div className="header" style={{
                fontSize: '20px',
                letterSpacing: '1px'
              }}>Units Label and Measures</div>
              <div className="tasks w-full">
                {
                  measureUnits.length > 0
                    ? measureUnits?.map((measureUnit, index) => (
                      /* eslint-disable-next-line */
                      <MeasureUnit measureUnit={measureUnit} index={index} key={index}
                        removeMeasureUnits={removeMeasureUnits}
                        updateMeasureUnits={updateMeasureUnits}
                      />
                    )) : ''
                }
              </div>
            </div>
          </div>
        </DialogContent>

        <DialogActions sx={{ margin: '10px', justifyContent: 'center' }}>
          <Button
            size="large"
            autoFocus
            onClick={() => {
              setOpen(false);
              setErrorObject({});
              loadData();
              // setMeasureUnits([]);
            }}
            style={{
              background: 'rgb(19 60 129)',}}
            sx={{
              color: 'white',
              padding: "8px 19px",
              marginRight: '10px',
              marginBottom: '35px',
              fontSize: '13px',
              borderRadius: '10px',
              fontWeight: '600',
              fontFamily: 'customfont',
              letterSpacing: '1px',
            }}
          >
            Cancel
          </Button>
          <Button
            style={{
              background: 'rgb(19 60 129)',}}
            sx={{
              color: 'white',
              padding: "8px 30px",
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

            disabled={errorObject?.categoryName?.errorStatus || errorObject?.categoryDescription?.errorStatus}
            size="large"
            type="submit"
          >
            {' '}
            {isAddButton ? 'Add' : 'Update'}
          </Button>

        </DialogActions>
      </form>
      {/* <NotificationBar
        handleClose={handleClose}
        notificationContent={openNotification.message}
        openNotification={openNotification.status}
        type={openNotification.type}
      /> */}
    </Dialog>
  );
}

export default SensorCategoryModal;
