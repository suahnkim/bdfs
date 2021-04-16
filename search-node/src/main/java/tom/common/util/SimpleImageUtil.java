package tom.common.util;

import java.awt.image.BufferedImage;
import java.io.BufferedOutputStream;
import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.OutputStream;

import javax.imageio.IIOImage;
import javax.imageio.ImageIO;
import javax.imageio.ImageWriteParam;
import javax.imageio.ImageWriter;
import javax.imageio.stream.ImageOutputStream;

import org.imgscalr.Scalr;

public class SimpleImageUtil {

	
	
	public static void resizeSquare(BufferedImage orgImage, int width, float quality, File outFile) throws GenException {
		try {
			int oHeight = orgImage.getHeight();
			int oWidth  = orgImage.getWidth();
			
			BufferedImage targetImage = null;
			//System.out.println(">>> oHeight["+oHeight+"], oWidth["+oWidth+"] oType["+orgImage.getPropertyNames()+"]");
			if(oHeight != oWidth) {
				int minLength = oWidth;
				int cropX = 0;
				int cropY = (oHeight-minLength)/2;
				if(oHeight < oWidth) {
					minLength = oHeight;
					cropX = (oWidth-minLength)/2;
					cropY = 0;
				}
				//System.out.println(">>> minLength["+minLength+"] originalImage.getTransparency()["+originalImage.getTransparency()+"]");
				targetImage = Scalr.crop(orgImage, cropX, cropY, minLength, minLength);
			} else {
				targetImage = orgImage;
			}
			
			
			BufferedImage resizeImage = Scalr.resize(targetImage, Scalr.Method.ULTRA_QUALITY, width,  width);
			
			ImageWriter jpgWriter = ImageIO.getImageWritersByFormatName("jpg").next();
			ImageWriteParam jpgWriteParam = jpgWriter.getDefaultWriteParam();
			jpgWriteParam.setCompressionMode(ImageWriteParam.MODE_EXPLICIT);
			jpgWriteParam.setCompressionQuality(quality);
			
			//jpgWriter.setOutput(new FileImageOutputStream(outFile));
			//jpgWriter.write(null, new IIOImage(resizeImage, null, null), jpgWriteParam);	
			
			OutputStream os = new BufferedOutputStream(new FileOutputStream(outFile));
			ImageOutputStream ios =  ImageIO.createImageOutputStream(os);
			jpgWriter.setOutput(ios);
			jpgWriter.write(null, new IIOImage(resizeImage, null, null), jpgWriteParam);	
			jpgWriter.reset();
			ios.close();
			os.close();
			
			
		}catch (Exception e) {
			throw new GenException(GenException.INTERNAL_ERROR, "image resize error " + e.getMessage(), e);
		}
	}
	
	public static void resizeHeight(BufferedImage orgImage, int height, float quality, File outFile) throws GenException {
		try {
			int oWidth  = orgImage.getWidth();
			int oHeight = orgImage.getHeight();
			float ratio = (float)oWidth/(float)oHeight;
			int resizeWidth = (int)(height * ratio);
			
			BufferedImage resizeImage = Scalr.resize(orgImage, Scalr.Method.ULTRA_QUALITY, resizeWidth,  height);
			
			ImageWriter jpgWriter = ImageIO.getImageWritersByFormatName("jpg").next();
			ImageWriteParam jpgWriteParam = jpgWriter.getDefaultWriteParam();
			jpgWriteParam.setCompressionMode(ImageWriteParam.MODE_EXPLICIT);
			jpgWriteParam.setCompressionQuality(quality);
			
			//jpgWriter.setOutput(new FileImageOutputStream(outFile));
			//jpgWriter.write(null, new IIOImage(resizeImage, null, null), jpgWriteParam);	
			
			OutputStream os = new BufferedOutputStream(new FileOutputStream(outFile));
			ImageOutputStream ios =  ImageIO.createImageOutputStream(os);
			jpgWriter.setOutput(ios);
			jpgWriter.write(null, new IIOImage(resizeImage, null, null), jpgWriteParam);	
			jpgWriter.reset();
			ios.close();
			os.close();
			
			
		}catch (Exception e) {
			throw new GenException(GenException.INTERNAL_ERROR, "image resize error " + e.getMessage(), e);
		}
	}
	
	
	
	
	
	public static void resizeWidthHeight(BufferedImage orgImage, int weight, int height, float quality, File outFile) throws GenException {
		try {
			int oWidth  = orgImage.getWidth();
			int oHeight = orgImage.getHeight();
			int newHeight = (int)(oWidth/1.91f);
			System.out.println("oWidth["+oWidth+"], oHeight["+oHeight+"]  newHeight["+newHeight+"]");
			
			int cropHeight = oHeight - newHeight;
			int cropPos = cropHeight/2;
			System.out.println("cropHeight["+cropHeight+"]");
			
			
			BufferedImage cropImage = Scalr.crop(orgImage, 0, cropPos, oWidth, newHeight);
			BufferedImage resizeImage = Scalr.resize(cropImage, Scalr.Method.ULTRA_QUALITY, weight,  height);
			
			ImageWriter jpgWriter = ImageIO.getImageWritersByFormatName("jpg").next();
			ImageWriteParam jpgWriteParam = jpgWriter.getDefaultWriteParam();
			jpgWriteParam.setCompressionMode(ImageWriteParam.MODE_EXPLICIT);
			jpgWriteParam.setCompressionQuality(quality);
			
			//jpgWriter.setOutput(new FileImageOutputStream(outFile));
			//jpgWriter.write(null, new IIOImage(resizeImage, null, null), jpgWriteParam);
			
			OutputStream os = new BufferedOutputStream(new FileOutputStream(outFile));
			ImageOutputStream ios =  ImageIO.createImageOutputStream(os);
			jpgWriter.setOutput(ios);
			jpgWriter.write(null, new IIOImage(resizeImage, null, null), jpgWriteParam);	
			jpgWriter.reset();
			ios.close();
			os.close();
			
			
		}catch (Exception e) {
			throw new GenException(GenException.INTERNAL_ERROR, "image resize error " + e.getMessage(), e);
		}
	}
	
	public static void genFacebookShareImage(BufferedImage orgImage, File outFile) throws IOException {
		int orgHeight = orgImage.getHeight();
		int orgWidth  = orgImage.getWidth();
		
		int resizeHeight = (int)(orgWidth * 0.525);
		int marginHeight = ((orgHeight - resizeHeight)/2);
		
		BufferedImage cropImage = Scalr.crop(orgImage, 0, marginHeight, orgWidth, resizeHeight);
		BufferedImage resizeImage = Scalr.resize(cropImage, Scalr.Method.ULTRA_QUALITY, 1200);
		
		ImageWriter jpgWriter = ImageIO.getImageWritersByFormatName("jpg").next();
		ImageWriteParam jpgWriteParam = jpgWriter.getDefaultWriteParam();
		jpgWriteParam.setCompressionMode(ImageWriteParam.MODE_EXPLICIT);
		jpgWriteParam.setCompressionQuality(0.7f);
		
		OutputStream os = new BufferedOutputStream(new FileOutputStream(outFile));
		ImageOutputStream ios =  ImageIO.createImageOutputStream(os);
		
		jpgWriter.setOutput(ios);
		jpgWriter.write(null, new IIOImage(resizeImage, null, null), jpgWriteParam);
		jpgWriter.reset();
		ios.close();
		os.close();
	}
	
}
