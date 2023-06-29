import {
  Button, Dialog, DialogContent, DialogTitle, TextField,
} from '@mui/material';
import React, { useEffect, useState } from 'react';
import { GasCylinderAddService, GasCylinderEditService } from '../../services/LoginPageService';

function GasCylinderModal({
  open, isAddButton, setOpen, setNotification, editData, setEditData, setRefreshData
}) {
  const [id, setId] = useState('');
  const [gasCylinderName, setGasCylinderName] = useState('');
  const [expiryDate, setExpiryDate] = useState('');

  useEffect(() => {
    if (editData) {
      setId(editData.id || '');
      setGasCylinderName(editData.gasCylinderName || '');
      setExpiryDate(editData.expiryDate || '');
    }
  }, [editData]);

  const onCancel = () => {
    setEditData({});
    setId('');
    setGasCylinderName('');
    setExpiryDate('');
    setOpen(false);
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (isAddButton === true) {
      GasCylinderAddService({ gasCylinderName, expiryDate }, handleSuccess, handleException);
    } else {
      GasCylinderEditService({ id, gasCylinderName, expiryDate }, handleSuccess, handleException);
    }
    setOpen(false);
  };

  const handleSuccess = (dataObject) => {
    setRefreshData(oldvalue=>!oldvalue);
    setNotification({
      status: true,
      type: 'success',
      message: dataObject.message,
    });
    setTimeout(() => {
      handleClose();
      onCancel();
    }, 5000);
  };

  const handleException = (errorObject, errorMessage) => {
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

  return (
    <Dialog
      sx={{ '& .MuiDialog-paper': { minWidth: '50%' } }}
      maxWidth="sm"
      open={open}
    >
      <DialogTitle>
        {isAddButton ? 'Add Cylinder' : 'Edit Cylinder'}
      </DialogTitle>
      <DialogContent>
        <form onSubmit={handleSubmit}>
          <TextField
            sx={{ mt: 1 }}
            type="text"
            fullWidth
            label="Cylinder Name"
            value={gasCylinderName}
            required
            onChange={(e) => { setGasCylinderName(e.target.value); }}
          />
          <TextField
            sx={{ mt: 2 }}
            type="date"
            fullWidth
            label="Expiry Date"
            value={expiryDate}
            required
            onChange={(e) => { setExpiryDate(e.target.value); }}
            InputLabelProps={{
              shrink: true,
            }}
          />
          <div className="rounded-md -space-y-px float-right" style={{ marginTop: 5 }}>
            <Button
              type="submit"
            >
              {isAddButton ? 'Add' : 'Update'}
            </Button>
            <Button
              onClick={onCancel}
            >
              Cancel
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  );
}

export default GasCylinderModal;
