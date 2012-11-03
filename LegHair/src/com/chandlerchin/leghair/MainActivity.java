package com.chandlerchin.leghair;

import java.io.IOException;
import java.util.ArrayList;
import java.util.List;

import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.NameValuePair;
import org.apache.http.client.ClientProtocolException;
import org.apache.http.client.HttpClient;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.message.BasicNameValuePair;
import org.apache.http.util.EntityUtils;

import android.os.AsyncTask;
import android.os.Bundle;
import android.app.Activity;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.TextView;

public class MainActivity extends Activity {

	Button setupBtn;
	TextView displayTv;
	
    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
        
        setupBtn = (Button) findViewById(R.id.btnSignUp);
        displayTv = (TextView) findViewById(R.id.tvOutput);
        setupBtn.setOnClickListener(new View.OnClickListener() {
			public void onClick(View v) {
				EditText etEmail = (EditText) findViewById(R.id.etEmail);
				EditText etPassword = (EditText) findViewById(R.id.etPassword);
				String email = etEmail.getText().toString();
				String password = etPassword.getText().toString();
				String output = "sending registration information:\n";
				output += "Email: " + email + "\n";
				output += "Password: " + password + "\n";
				output += "Results: " + new SignUpTask().execute(email, password);
				displayTv.setText(output);
			}
		});
    }
    
   
    
    private class SignUpTask extends AsyncTask<String, Integer, String> {
        protected String doInBackground(String... data) {
            String email = data[0];
            String password = data[1];
            return postData(email, password);
        }

        protected void onProgressUpdate(Integer... progress) {
        }

        protected void onPostExecute(String result) {
        	displayTv.setText(result);
        }
        
        private String postData(String email, String password) {
            // Create a new HttpClient and Post Header
            HttpClient httpclient = new DefaultHttpClient();
            HttpPost httppost = new HttpPost("http://chandlerchin.com/test/testservice.php");

            try {
                // Add your data
                List<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(2);
                nameValuePairs.add(new BasicNameValuePair("user_name", email));
                nameValuePairs.add(new BasicNameValuePair("password", password));
                httppost.setEntity(new UrlEncodedFormEntity(nameValuePairs));

                // Execute HTTP Post Request
                HttpResponse response = httpclient.execute(httppost);
                HttpEntity responseEntity = response.getEntity();
                if(responseEntity!=null) {
                    return EntityUtils.toString(responseEntity);
                } else {
                	return "Response Entity shouldn't be null";
                }
                
            } catch (ClientProtocolException e) {
            	return e.toString();
            } catch (IOException e) {
            	return e.toString();
            } catch (Exception e) {
            	return e.toString();
            }
        } 
    }

}
