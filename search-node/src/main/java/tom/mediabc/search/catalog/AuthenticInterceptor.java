package tom.mediabc.search.catalog;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.web.servlet.ModelAndView;
import org.springframework.web.servlet.handler.HandlerInterceptorAdapter;

import tom.common.configuration.LoggerName;

public class AuthenticInterceptor extends HandlerInterceptorAdapter {

	
	private Logger log = LoggerFactory.getLogger(LoggerName.SVC);
	
	
	@Override
	public boolean preHandle(HttpServletRequest request, HttpServletResponse response, Object handler) throws Exception {
		LoginInfo loginInfo = (LoginInfo)request.getSession().getAttribute(LoginInfo.SESSION_USER);
		log.debug("preHandle >>>> " + request.getRequestURI() + ":: " + loginInfo);
		if(loginInfo == null) {
			response.sendRedirect("/ccsearch/catalog/login/sign.do");
		}
		
		
		return true;
	}

	/**
	 * This implementation is empty.
	 */
	@Override
	public void postHandle(HttpServletRequest request, HttpServletResponse response, Object handler, ModelAndView modelAndView) throws Exception {
	
		//log.debug("postHandle >>>>" + request.getRequestURI());
	}

	/**
	 * This implementation is empty.
	 */
	@Override
	public void afterCompletion(
			HttpServletRequest request, HttpServletResponse response, Object handler, Exception ex)
			throws Exception {
		
		
		//log.debug("afterCompletion >>>>" + request.getRequestURI());
	}

	/**
	 * This implementation is empty.
	 */
	@Override
	public void afterConcurrentHandlingStarted(
			HttpServletRequest request, HttpServletResponse response, Object handler)
			throws Exception {
		
		
		//log.debug("afterConcurrentHandlingStarted >>>>" + request.getRequestURI());
	}
}
