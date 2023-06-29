import { GppMaybe } from '@mui/icons-material';
import {
  Button, Dialog, DialogActions, DialogContent, DialogTitle, Typography,
} from '@mui/material';
import React from 'react';

function ConfirmModeChange({
  modeChange, setModeChange, changeDeviceId, changeDeviceIdMode, ChangeModeAPI,
}) {
  const handleConfirm = () => {
    ChangeModeAPI(changeDeviceId, changeDeviceIdMode);
  };
  return (
    <Dialog
      fullWidth
      maxWidth="sm"
      sx={{ '& .MuiDialog-paper': { width: '80%', maxHeight: '100%' } }}
      open={modeChange}
    >
      <DialogTitle sx={{ textAlign: 'center' }}>
        <GppMaybe color="warning" style={{ fontSize: 95 }} />
      </DialogTitle>
      <DialogContent sx={{ textAlign: 'center' }}>
        <Typography
          sx={{ m: 1 }}
          variant="h5"
          component="span"
        >
          Do you really want to change the mode?
        </Typography>
      </DialogContent>
      <DialogActions sx={{ margin: '10px' }}>
        <div style={{ textAlign: 'center' }}>
          <Button onClick={() => {
            handleConfirm();
          }}
          >
            Confirm
          </Button>
          <Button
            onClick={() => { setModeChange(false); }}
          >
            Cancel
          </Button>
        </div>
      </DialogActions>
    </Dialog>
  );
}

export default ConfirmModeChange;
