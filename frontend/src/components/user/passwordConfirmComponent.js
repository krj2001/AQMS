import {
  Button, Dialog, DialogContent, DialogTitle, TextField,
} from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';

function ConfirmPassword({
  open, passwordSubmit, setConfirmPassword, setBtnReset,
}) {

  const Buttons = styled(Button)(
    () => ({

      height: '0',
      color: 'white',
      padding: "20px 19px",
      fontSize: '13px',
      borderRadius: '10px',
      fontWeight: '600',
      fontFamily: 'customfont',
      letterSpacing: '1px',
      boxShadow: 'none',
      marginRight: '20px',
      marginBottom: '20px'

    })
  );
  return (
    <Dialog
      maxWidth="sm"
      open={open}
    >
      <DialogTitle sx={{fontFamily:'customfont', fontWeight:'600', letterSpacing:'1px'}}>
        Confirm your password
      </DialogTitle>
      <DialogContent>
        <form onSubmit={passwordSubmit}>
          <div className="col-span-6 sm:col-span-2 lg:col-span-2 ">
            <div className="inline">
              <TextField
                placeholder="Enter your password"
                type="password"
                required
                onChange={(e) => {
                  setConfirmPassword(e.target.value);
                }}
              />
            </div>
          </div>
          <div className="mt-5 ml-0 float-right">
            <Buttons
            style={{
              background: 'rgb(19 60 129',}}
              onClick={() => {
                setBtnReset(false);
              }}
            >
              Cancel
            </Buttons>
            <Buttons
            style={{
              background: 'rgb(19 60 129',}}
              type="submit"
            >
              Submit
            </Buttons>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  );
}

export default ConfirmPassword;
